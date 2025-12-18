<?php

namespace App\Nova;

use Eminiarts\Tabs\Tabs;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Country extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Country>
     */
    public static $model = \App\Models\Country::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->name . ' (' . $this->phone_code . ')';
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'country_code',
        'phone_code'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {

        return [
            (new Tabs('Main Details', [
                'Main Details' => [
                    // ID::make()->sortable(),

                    Text::make('Arabic Name', 'name_ar')
                        ->rules(['required'])
                        ->withMeta(['extraAttributes' => ['dir' => 'rtl']])
                        ->required()
                        ->sortable(),

                    Text::make('English Name', 'name')
                        ->rules(['required'])
                        ->required()
                        ->sortable(),

                    Text::make('Code', 'country_code')
                        ->rules(['required'])
                        ->required()
                        ->sortable(),

                    Text::make('Phone Code', 'phone_code')
                        ->rules(['required'])
                        ->required()
                        ->sortable(),

                    Text::make('Phone Digits', 'phone_digits')
                        ->rules(['required'])
                        ->required()
                        ->sortable(),

                    Boolean::make('Is Active', 'is_active')
                        ->filterable(),
                ]
            ]))->withToolbar(),

            (new Tabs('Additional Details', [
                'Users' =>
                HasMany::make('Users', 'users', User::class),
            ]))

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
