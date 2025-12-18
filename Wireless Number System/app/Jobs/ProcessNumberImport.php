<?php

namespace App\Jobs;

use App\Mail\NewNumberAdded;
use App\Models\Number;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL as NovaURL;

class ProcessNumberImport  implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Collection $records;
    protected $fields;
    protected $userId;
    protected $authUser;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $records, $fields, $userId, $authUser)
    {
        $this->records = $records;
        $this->fields = $fields;
        $this->userId = $userId;
        $this->authUser = $authUser;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $numberIds = [];

            // Process records in chunks of 300 for better performance
            $this->records->chunk(300)->each(function ($chunk) use (&$numberIds) {
                $chunkNumberIds = [];


                // Use bulk insert for better performance
                $numbersToInsert = [];

                foreach ($chunk as $record) {
                    $numbersToInsert[] = [
                        'phone_number' => $record['number'],
                        'account_number' => $record['account_number'],
                        'pin' => $record['pin'],
                        'user_id' => $this->userId,
                        'city_id' => $this->fields['city'],
                        'carrier_id' => $this->fields['carrier'],
                        'expiry' => $this->fields['expiry'],
                        'is_expired' => Carbon::parse($this->fields['expiry'])->toDateString() < Carbon::now()->toDateString(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Bulk insert the numbers
                Number::insert($numbersToInsert);

                // Get the inserted IDs
                $insertedNumbers = Number::whereIn('phone_number', $chunk->pluck('number'))
                    ->where('user_id', $this->userId)
                    ->pluck('id');

                $chunkNumberIds = $insertedNumbers->toArray();
                $numberIds = array_merge($numberIds, $chunkNumberIds);
            });

            // Create order
            $order = new Order();
            $order->user_id = $this->userId;
            $order->carrier_id = $this->fields['carrier'];
            $order->city_id = $this->fields['city'];
            $order->order_type = Order::ORDER_TYPE_SELL;
            $order->price = $this->fields['price'] ?? 0;
            $order->subtotal = count($numberIds) * $order->price;
            $order->total = count($numberIds) * $order->price;
            $order->total_qty = count($numberIds);
            $order->status = Order::STATUS_COMPLETED;
            $order->save();

            // Sync numbers with order
            $order->numbers()->sync($numberIds);

            // Send notifications if seller
            if ($this->authUser->role == User::SELLER_ROLE) {
                $this->sendNotifications($order);
            }

            // Dispatch auto assign job
            \App\Jobs\AutoAssignNumbers::dispatch();
        } catch (\Throwable $th) {
            Log::error('Error processing number import: ' . $th->getMessage());
            throw $th;
        }
    }

    /**
     * Send notifications to super admin
     */
    private function sendNotifications($order): void
    {
        try {
            $user = User::IsSuperAdminRole()->first();
            if ($user) {
                $user->notify(
                    NovaNotification::make()
                        ->message('New numbers uploaded by ' . $this->authUser->name)
                        ->action('View', NovaURL::remote('/dashboard/resources/orders/' . $order->id))
                        ->icon('info')
                        ->type('success')
                );
            }

            Mail::send(new NewNumberAdded(config('app.url') . '/dashboard/resources/orders/' . $order->id));
        } catch (\Exception $e) {
            Log::error('Error sending new number added notification: ' . $e->getMessage());
        }
    }
}
