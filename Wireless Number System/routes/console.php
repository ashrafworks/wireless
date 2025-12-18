<?php

use App\Jobs\ExpireNumberJob;
use App\Jobs\OrderRefundedJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote');

Schedule::job(OrderRefundedJob::class)->everyTwoHours();

Schedule::job(ExpireNumberJob::class)->everyThreeMinutes();
