<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Transaction;
use App\Models\WalletHistory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class OrderRefundedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //Log::info('Refund process started.');

        Order::isNotRefunded()
            ->isPending()
            ->isBuying()
            ->where('created_at', '<=', now()->subHours(30))
            // ->where('created_at', '<=', now()->subMinutes(5))
            ->chunk(300, function ($orders) {
                foreach ($orders as $order) {
                    try {
                        // Log::info("Processing order ID: {$order->id}");

                        $wallet = $order->user->wallet;
                        $successPrice = $order->carrier->price * ($order->success_qty ?? 0);
                        $remainingPrice = $order->total - $successPrice;

                        //   Log::info("Order ID: {$order->id} - Success Price: {$successPrice}, Remaining Price: {$remainingPrice}");

                        // Update wallet balances
                        $wallet->available += $remainingPrice;
                        $wallet->used -= $remainingPrice;
                        $wallet->save();

                        // Create wallet history record
                        WalletHistory::create([
                            'wallet_id' => $wallet->id,
                            'user_id' => $order->user_id,
                            'amount' => $remainingPrice,
                            'status' => WalletHistory::STATUS_APPROVED,
                            'currency' => $order->currency,
                            'model_id' => $order->id,
                            'model_type' => Order::class,
                            'type' => WalletHistory::TYPE_REFUND,
                            'description' => 'Order has been refunded by admin',
                        ]);

                        // Calculate rejected quantity
                        $rejectQty = ($order->success_qty !== null && $order->success_qty > 0)
                            ? max(0, $order->total_qty - $order->success_qty)
                            : $order->total_qty;

                        $transaction = $order->transaction;
                        if ($transaction) {
                            $transaction->status = Transaction::CANCELLED;
                            $transaction->save();
                        }

                        // Update order status
                        $order->update([
                            'status' => Order::STATUS_REFUNDED,
                            'is_refunded' => true,
                            'refunded_at' => now(),
                            'rejected_at' => null,
                            'reject_qty' => $rejectQty,
                            'subtotal' => $successPrice,
                            'total' => $successPrice,
                            'notes' => 'Order has been refunded by admin',
                        ]);

                        //  Log::info("Order ID: {$order->id} updated successfully.");
                    } catch (\Exception $e) {
                        Log::error("Error processing order ID: {$order->id}. Error: " . $e->getMessage());
                    }
                }
            });

        // Log::info('Refund process completed.');
    }
}
