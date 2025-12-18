<?php

namespace App\Nova\Metrics;

use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;
use Laravel\Nova\Nova;

class WalletBalance extends Value
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): ValueResult
    {
       $wallet = $request->user()->wallet;


        return $this->result($wallet->available)->format('0,0000')->prefix('$');
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int|string, string>
     */
    public function ranges(): array
    {
        return [
            // 30 => Nova::__('30 Days'),
            // 60 => Nova::__('60 Days'),
            // 365 => Nova::__('365 Days'),
            // 'TODAY' => Nova::__('Today'),
            // 'MTD' => Nova::__('Month To Date'),
            // 'QTD' => Nova::__('Quarter To Date'),
            // 'YTD' => Nova::__('Year To Date'),
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     */
    public function cacheFor(): DateTimeInterface|null
    {
        // return now()->addMinutes(5);

        return null;
    }
}
