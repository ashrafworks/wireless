<?php

namespace App\Nova\Metrics;

use App\Models\Country;
use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;
use Laravel\Nova\Metrics\PartitionResult;

class UserPerCountry extends Partition
{
    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request): PartitionResult
    {
        $data = Country::query()->isActive();

        if ($request->user() && in_array($request->user()->role, [\App\Models\User::SELLER_ROLE, \App\Models\User::USER_ROLE])) {
            $data = $data->withCount(['users' => function ($query) use ($request) {
                $query->where('role', $request->user()->role)
                    ->where('parent_user_id', $request->user()->id);
            }]);
        } else {
            $data = $data->withCount(['users' => function ($query) {
                $query->whereIn('role', [\App\Models\User::SELLER_ROLE, \App\Models\User::USER_ROLE]);
            }]);
        }

        $data = $data->pluck('users_count', 'name')->toArray();

        return $this->result($data);
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     */
    public function cacheFor(): DateTimeInterface|null
    {
        // return now()->addMinutes(5);

        return null;
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'user-per-country';
    }
}
