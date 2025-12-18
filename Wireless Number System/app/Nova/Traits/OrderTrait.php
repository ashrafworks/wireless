<?php


namespace App\Nova\Traits;

use App\Nova\Actions\CopyOrderNumber;
use App\Nova\Actions\ExportOrderNumbers;
use App\Nova\Area;
use App\Nova\Buyer;
use App\Nova\Carrier;
use App\Nova\City;
use App\Nova\Country;
use App\Nova\Number as NovaNumber;
use App\Nova\Seller;
use App\Nova\User;
use App\Nova\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

use Pavloniym\ActionButtons\ActionButtons;

trait OrderTrait
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Order::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function commonFields(NovaRequest $request, $extraFields = [])
    {
        return [

            Text::make('Reference', 'reference')
                ->sortable()
                ->rules('required', 'max:255')
                ->exceptOnForms(),

            Stack::make('User', 'user', [
                Line::make('Name', function () {
                    return $this->user ? $this->user->name : '';
                }),
                Line::make('Email', function () {
                    return $this->user ? $this->user->email : '';
                })
                    ->extraClasses('italic font-medium text-80')
                    ->asSmall()
                    ->onlyOnIndex(),
                Line::make('Phone Number', function () {
                    return $this->user ? $this->user->phone_number : '';
                })
                    ->extraClasses('italic font-medium text-80')
                    ->asSmall()
                    ->onlyOnIndex(),
            ])
                ->onlyOnIndex()
                ->sortable(),


            Stack::make('Quantity', 'total_qty', [

                Text::make('Quantity', function () {
                    return '<span class="text-green-500">' . $this->success_qty . '</span> / <span class="text-red-500">' . $this->reject_qty . '</span> / <span class="text-gray-500">' . ($this->total_qty - ($this->reject_qty + $this->success_qty)) . '</span> / <span class="text-sky-600">' . $this->total_qty . '</span>';
                })->asHtml()->exceptOnForms(),
            ])
                ->sortable()
                ->canSee(function () {
                    return $this->order_type == \App\Models\Order::ORDER_TYPE_BUY;
                }),

            Text::make('Quantity', 'total_qty')
                ->sortable()
                ->exceptOnForms()
                ->canSee(function () {
                    return $this->order_type == \App\Models\Order::ORDER_TYPE_SELL;
                }),

            BelongsTo::make('User', 'user', User::class)
                ->required()
                ->rules('required')
                ->hideFromIndex()
                ->filterable()
                ->searchable(),

            BelongsTo::make('Carrier', 'carrier', Carrier::class)
                ->required()
                ->rules('required')
                ->searchable()
                ->filterable(),

            // BelongsTo::make('Area', 'area', Area::class)
            //     ->required()
            //     ->rules('required')
            //     ->filterable(),

            BelongsTo::make('City', 'city', City::class)
                ->required()
                ->rules('required')
                ->searchable()
                ->filterable(),

            Number::make('Quantity', 'total_qty')
                ->rules('required', 'numeric', 'min_digits:1')
                ->required()
                ->onlyOnForms(),

            Text::make('Currency', 'currency')
                ->rules('required', 'max:255')
                ->required()
                ->onlyOnDetail(),

            Currency::make('Per Number Cost', 'price')
                ->symbol('USD')
                ->rules('required', 'numeric')
                ->required()
                ->exceptOnForms(),

            Currency::make('Sub Total', 'subtotal')
                ->symbol('USD')
                ->rules('required', 'numeric')
                ->required()
                ->onlyOnDetail(),

            Currency::make('Total', 'total')
                ->symbol('USD')
                ->rules('required', 'numeric')
                ->required()
                ->exceptOnForms(),

            Select::make('Status', 'status')
                ->options(\App\Models\Order::GET_STATUS())
                ->displayUsingLabels()
                ->rules('required')
                ->filterable()
                ->required(),


            Textarea::make('Notes', 'notes')
                ->rules('nullable')
                ->nullable(),


            Boolean::make('Is Refunded', 'is_refunded')
                ->filterable()
                ->sortable()
                ->exceptOnForms()
                ->canSee(function ($request) {
                    return  in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]) && $this->order_type == \App\Models\Order::ORDER_TYPE_BUY;
                }),

            DateTime::make('Refunded At', 'refunded_at')
                ->displayUsing(function ($refunded_at) {
                    return $refunded_at ? Carbon::parse($refunded_at)->format('Y-m-d H:i A') : null;
                })
                ->sortable()
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return  in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]) && $this->order_type == \App\Models\Order::ORDER_TYPE_BUY;
                }),

            DateTime::make('Created At', 'created_at')
                ->displayUsing(function ($created_at) {
                    return $created_at ? Carbon::parse($created_at)->format('Y-m-d H:i A') : null;
                })
                ->sortable()
                ->exceptOnForms(),

            ...$extraFields
        ];
    }




    public function numbers()
    {
        return BelongsToMany::make('Numbers', 'numbers', NovaNumber::class);
    }
}
