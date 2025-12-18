<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AutoAssignNumbers implements ShouldQueue
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
        \App\Models\Order::query()
            ->isRemaining()
            ->isPending()
            ->isBuying()
            ->isNotRefunded()
            ->orderBy('id', 'asc')
            ->chunk(300, function ($orders) {
                $this->processOrderChunk($orders);
            });
    }

    /**
     * Process a chunk of orders more efficiently
     */
    private function processOrderChunk($orders): void
    {
        $updates = [];
        $numberUpdates = [];
        
        foreach ($orders as $order) {
            $remaining = $order->total_qty - $order->success_qty;
            
            if ($remaining <= 0) {
                continue;
            }
            
            $numberIds = \App\Models\Number::isNotUsed()
                ->where('carrier_id', $order->carrier_id)
                ->where('city_id', $order->city_id)
                ->isNotExpired()
                ->NotExpired()
                ->limit($remaining)
                ->pluck('id')
                ->toArray();
                
            if (count($numberIds) > 0) {
                $newSuccessQty = $order->success_qty + count($numberIds);
                $newStatus = ($order->total_qty == $newSuccessQty) 
                    ? \App\Models\Order::STATUS_COMPLETED 
                    : $order->status;
                
                // Prepare bulk updates
                $updates[] = [
                    'id' => $order->id,
                    'success_qty' => $newSuccessQty,
                    'status' => $newStatus,
                    'updated_at' => now(),
                ];
                
                // Sync numbers with order
                $order->numbers()->sync($numberIds);
                
                // Prepare number updates
                $numberUpdates = array_merge($numberUpdates, $numberIds);
            }
        }
        
        // Bulk update orders
        if (!empty($updates)) {
            \App\Models\Order::upsert($updates, ['id'], ['success_qty', 'status', 'updated_at']);
        }
        
        // Bulk update numbers
        if (!empty($numberUpdates)) {
            \App\Models\Number::whereIn('id', $numberUpdates)->update(['is_used' => true]);
        }
    }
}
