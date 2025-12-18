<?php

namespace App\Jobs;

use App\Models\Number;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ExpireNumberJob implements ShouldQueue
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
        //Log::info('Number expiration process started.');

        Number::whereNotNull('expiry')
            ->expired()
            ->isNotExpired()
            ->isNotUsed()
            ->chunk(300, function ($numbers) {
                foreach ($numbers as $number) {
                    try {
                       // Log::info("Processing Number ID: {$number->id}");

                        $number->is_expired = true;
                        $number->save();

                        // Log::info("Number ID: {$number->id} marked as expired.");
                    } catch (\Exception $e) {
                        Log::error("Error processing Number ID: {$number->id}. Error: " . $e->getMessage());
                    }
                }
            });

       // Log::info('Number expiration process completed.');
    }
}
