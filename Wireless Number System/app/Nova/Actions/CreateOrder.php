<?php

namespace App\Nova\Actions;

use App\Mail\NewOrderNotification;
use App\Models\Number as ModelsNumber;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL as NovaURL;

class CreateOrder extends Action
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
        $userId = $fields->buyer;
        $numberIds =  ModelsNumber::isNotUsed()
            ->where('carrier_id', $fields->carrier)
            ->where('city_id', $fields->city)
            ->isNotExpired()->limit($fields->quantity)->get()->pluck('id')->toArray();
        $authUser = Auth::user();

        if ($authUser->role == User::USER_ROLE) {
            if (!$authUser->parent_user_id) {
                $userId = $authUser->id;
            } elseif ($authUser->parent_user_id) {
                $userId = $authUser->parent_user_id;
            }
        }


        $order = new  Order();
        $order->user_id = $userId;
        $order->carrier_id = $fields->carrier;
        $order->area_id = $fields->area;
        $order->city_id = $fields->city;
        $order->price = $order->carrier?->price ?? 0;
        $order->order_type = \App\Models\Order::ORDER_TYPE_BUY;
        $order->total_qty = $fields->quantity;
        $order->success_qty = count($numberIds);
        $order->status = \App\Models\Order::STATUS_PENDING;


        if ($order->success_qty == $order->total_qty) {
            $order->status = \App\Models\Order::STATUS_COMPLETED;
        }
        $order->save();

        $order->numbers()->sync($numberIds);
        $data = ModelsNumber::whereIn('id', $numberIds)->update(['is_used' => true]);
        if ($authUser->role == User::USER_ROLE) {
            $user = User::IsSuperAdminRole()->first();
            $user->notify(
                NovaNotification::make()
                    ->message('New order created by ' . Auth::user()->name)
                    ->action('View', NovaURL::remote('/dashboard/resources/orders/' . $order->id))
                    ->icon('info')
                    ->type('success')
            );

            try {
                Mail::send(new NewOrderNotification(config('app.url') . '/dashboard/resources/orders/' . $order->id));
            } catch (\Exception $e) {
                Log::error('Error sending new order notification email: ' . $e->getMessage());
            }
        }

        return Action::message('Order created successfully.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [

            Select::make('Buyer', 'buyer')
                ->options(\App\Models\User::isBuyerRole()->isActive()->pluck('name', 'id'))
                ->rules('required')
                ->required()
                ->searchable()
                ->displayUsingLabels()
                ->fullWidth()
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                }),

            Select::make('Carrier', 'carrier')
                ->options(\App\Models\Carrier::isActive()->pluck('name', 'id'))
                ->rules('required')
                ->searchable()
                ->required()
                ->displayUsingLabels()
                ->fullWidth(),

            Currency::make('Carrier Price', 'carrier_price')
                ->fullWidth()
                ->symbol('USD')
                ->dependsOn('carrier', function (Text $field, NovaRequest $request) {
                    if ($request->carrier) {
                        $carrier = \App\Models\Carrier::find($request->carrier);
                        $field->withMeta(['value' => $carrier->price, 'readonly' => true])
                            ->required()
                            ->rules('required')
                            ->show();
                        return;
                    }
                    $field->hide();
                }),

            Select::make('City', 'city')
                ->options(\App\Models\City::isActive()->pluck('name', 'id'))
                ->rules('required')
                ->searchable()
                ->required()
                ->displayUsingLabels()
                ->fullWidth(),

            // Select::make('Area', 'area')
            //     ->options(\App\Models\Area::isActive()->pluck('code', 'id'))
            //     ->rules('required')
            //     ->required()
            //     ->displayUsingLabels()
            //     ->fullWidth(),

            Number::make('Quantity', 'quantity')
                ->rules('required')
                ->required()
                ->fullWidth(),

        ];
    }
}
