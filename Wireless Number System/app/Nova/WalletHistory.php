<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphTo;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class WalletHistory extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\WalletHistory>
     */
    public static $model = \App\Models\WalletHistory::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id','user_id','wallet_id','user.name','user.email'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            // ID::make()->sortable(),

            BelongsTo::make('User', 'user', User::class)
                ->required()
                ->rules('required'),

            BelongsTo::make('Wallet', 'wallet', Wallet::class)
                ->required()
                ->rules('required'),

            MorphTo::make('Model', 'model')
                ->types([
                    Order::class,
                ])
                ->nullable()
                ->rules('nullable'),

            Select::make('Type', 'type')
                ->options(\App\Models\WalletHistory::GET_TYPE())
                ->displayUsingLabels()
                ->sortable()
                ->rules('required')
                ->filterable(),

            Text::make('Currency', 'currency')
                ->sortable()
                ->rules('required'),

            Currency::make('Amount', 'amount')
                ->symbol('USD')
                ->sortable()
                ->rules('required'),

            Select::make('Status', 'status')
                ->options(\App\Models\WalletHistory::GET_STATUS())
                ->displayUsingLabels()
                ->sortable()
                ->rules('required')
                ->filterable(),

            Textarea::make('Description', 'description')
                ->sortable()
                ->rules('nullable'),


                DateTime::make('Created At')
                ->displayUsing(function ($value) {
                    return $value ? $value->format('Y-m-d H:i') : null;
                })
                ->exceptOnForms(),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
