<?php

namespace App\Observers;

use App\Mail\OrderRefund;
use App\Models\Number;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\WalletHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Nova\Notifications\NovaNotification;

class OrderObserver
{
    public function creating(Order $order)
    {
        $this->generateReference($order);
        if ($order->order_type == Order::ORDER_TYPE_BUY) {
            $this->genrateTotal($order);

            $wallet = $order->user->wallet;
            if ($wallet && ($wallet->available <= 0 || $wallet->available < $order->total)) {
                throw new \Exception('Insufficient wallet balance');
            }
        }
    }
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if ($order->order_type == Order::ORDER_TYPE_BUY) {
            $wallet = $order->user->wallet;
            $this->transaction($order);

            if ($wallet) {
                $wallet->available = $wallet->available - $order->total;
                $wallet->used = $wallet->used + $order->total;
                $wallet->save();

                $wallet->walletHistories()->create([
                    'amount' => $order->total,
                    'type' => WalletHistory::TYPE_DEBIT,
                    'description' => 'Order ' . $order->reference,
                    'user_id' => $order->user_id,
                    'currency' => $order->currency,
                    'model_id' => $order->id,
                    'model_type' => Order::class,
                    'status' => WalletHistory::STATUS_APPROVED,
                ]);
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        // Refund logic
        $numberIds = $order->numbers()->pluck('numbers.id')->toArray();
        // Handle number detachment/deletion based on role
        if (optional($order->user)->role === \App\Models\User::SELLER_ROLE) {
            // Just detach unused numbers
            $deatch =  $order->numbers()->where('numbers.is_used', false)->detach();

            $delte = $order->user->numbers()->where('is_used', false)->delete();
        } elseif (optional($order->user)->role === \App\Models\User::USER_ROLE) {
            // Delete unused numbers
            $order->numbers()->where('numbers.is_used', true)->detach();

            $admin = Auth::user();

            // Reset 'is_used' status of already used numbers
            Number::whereIn('id', $numberIds)
                ->where('is_used', true)
                ->update(['is_used' => false]);
            // Set success_qty to 0 and save
            $order->success_qty = 0;
            $order->save();

            // Full refund
            $wallet = $order->user->wallet;
            $wallet->available = $wallet->available + $order->total;
            $wallet->used = $wallet->used - $order->total;
            $wallet->save();

            WalletHistory::create([
                'wallet_id'   => $wallet->id,
                'user_id'     => $order->user_id,
                'amount'      => $order->total,
                'status'      => WalletHistory::STATUS_APPROVED,
                'currency'    => $order->currency,
                'model_id'    => $order->id,
                'model_type'  => Order::class,
                'type'        => WalletHistory::TYPE_REFUND,
                'description' => 'Full refund issued by admin',
            ]);

            $order->user->notify(
                NovaNotification::make()
                    ->message('Your order has been refunded successfully by '.($admin ?  $admin->name . " ( ".\App\Models\User::$rolesLables[$admin->role]." )" : "admin").'. Total amount refunded: $ '.$order->total)
                    ->type('success')
            );
            try {
                Mail::send(new OrderRefund($order->user->name, $order->total));
            } catch (\Exception $e) {
                Log::error('Error sending order refund email: ' . $e->getMessage());
            }

        }
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    private function generateReference(Order $order)
    {
        $order->reference = 'CS' . rand(1000, 9999);
    }

    private function genrateTotal(Order $order)
    {
        $price  = $order->carrier ? $order->carrier->price : 0;
        $order->subtotal = $order->total_qty * $price;
        $order->currency = Transaction::USD;
        $order->total = $order->subtotal;
    }

    private function transaction(Order $order)
    {
        Transaction::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'charged_price' => $order->total,
            'currency' => $order->currency,
            'origin' => Transaction::WALLET,
            'platform' => Transaction::ADMIN_DASHBOARD,
            'status' => Transaction::COMPLETED,
        ]);
    }
}
