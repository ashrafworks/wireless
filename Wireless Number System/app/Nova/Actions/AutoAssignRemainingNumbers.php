<?php

namespace App\Nova\Actions;

use App\Models\Number;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class AutoAssignRemainingNumbers extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        foreach ($models as  $order) {
            $remaining = $order->total_qty - $order->success_qty;
            $numberIds =  Number::isNotUsed()
                ->where('carrier_id', $order->carrier_id)
                ->where('city_id', $order->city_id)
                ->isNotExpired()->limit($remaining)->get()->pluck('id')->toArray();
            if (count($numberIds) > 0) {

                $order->success_qty = $order->success_qty + count($numberIds);
                if ($order->total_qty == $order->success_qty) {
                    $order->status = Order::STATUS_COMPLETED;
                }
                $order->save();

                $order->numbers()->sync($numberIds);

                Number::whereIn('id', $numberIds)->update(['is_used' => true]);
            }
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
