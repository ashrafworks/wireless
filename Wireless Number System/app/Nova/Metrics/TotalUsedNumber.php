<?php

namespace App\Nova\Metrics;

use App\Models\Number;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;
use Laravel\Nova\Nova;

class TotalUsedNumber  extends Partition
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $numbersQuery = Number::query()->when(
            $request->user() && $request->user()->role == \App\Models\User::SELLER_ROLE,
            function ($query) use ($request) {
                if ($request->user()->parent_user_id) {
                    return $query->where('user_id', $request->user()->parent_user_id);
                }
                return $query->where('user_id', $request->user()->id);
            }
        );

        // Clone the query to ensure they are independent
        $isUsed = (clone $numbersQuery)->isUsed()->count();
        $isNotUsed = (clone $numbersQuery)->isNotUsed()->count();

        return $this->result([
            'Used' => $isUsed,
            'Unused' => $isNotUsed,
        ])->colors(['#8fc15d', '#f5573b']);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int|string, string>
     */
    public function ranges(): array
    {
        return [
            30 => Nova::__('30 Days'),
            60 => Nova::__('60 Days'),
            365 => Nova::__('365 Days'),
            'TODAY' => Nova::__('Today'),
            'MTD' => Nova::__('Month To Date'),
            'QTD' => Nova::__('Quarter To Date'),
            'YTD' => Nova::__('Year To Date'),
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
