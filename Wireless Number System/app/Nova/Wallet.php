<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Wallet extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Wallet>
     */
    public static $model = \App\Models\Wallet::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public function title()  {
        return $this->user ? $this->user->name . '( '.$this->currency.' '.$this->available.' )' : $this->id;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id','user.name','currency'
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
            ->rules('required')
            ->filterable()
            ->searchable(),

            Text::make('Currency', 'currency')
            ->sortable()
            ->rules('required'),
            
            Currency::make('Used', 'used')
            ->symbol('USD')
            ->sortable()
            ->rules('required'),

            Currency::make('Available', 'available')
            ->symbol('USD')
            ->sortable()
            ->rules('required'),

            Currency::make('Total', 'total')
            ->symbol('USD')
            ->sortable()
            ->rules('required'),

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
