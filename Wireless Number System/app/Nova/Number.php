<?php

namespace App\Nova;

use App\Nova\Actions\AddNumber;
use App\Nova\Actions\CopyOrderNumber;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;


class Number extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Number>
     */
    public static $model = \App\Models\Number::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'phone_number';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'phone_number',
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

            BelongsTo::make('Carrier', 'carrier', Carrier::class)
                ->required()
                ->filterable()
                ->rules('required'),

            // BelongsTo::make('Area', 'area', Area::class)
            //     ->required()
            //     ->filterable()
            //     ->rules('required'),

            BelongsTo::make('City', 'city', City::class)
                ->required()
                ->filterable()
                ->rules('required'),

            BelongsTo::make('Seller', 'user', Seller::class)
                ->required()
                ->filterable()
                ->rules('required')
                ->exceptOnForms()
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                }),

            Text::make('Phone Number', 'phone_number')
                ->required()
                ->rules('required'),

            Text::make('Account Number', 'account_number')
                ->required()
                ->rules('required'),

            Text::make('Pin', 'pin')
                ->required()
                ->rules('required'),

            Boolean::make('Expired', 'is_expired')
                ->required()
                ->rules('required')
                ->exceptOnForms(),

            Boolean::make('Used', 'is_used')
                ->filterable()
                ->rules('nullable')
                ->exceptOnForms()
                ->canSee(function () use ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                }),


            DateTime::make('Expiry At', 'expiry')
                ->displayUsing(function ($value) {
                    return $value ? Carbon::parse($value)->format('Y-m-d') : null;
                })
                ->exceptOnForms(),

            DateTime::make('Created At')
                ->displayUsing(function ($value) {
                    return $value ? $value->format('Y-m-d H:i') : null;
                })
                ->onlyOnDetail(),

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
        return [
            (new AddNumber())
                ->standalone()
                ->onlyOnIndex()
                ->canSee(function () use ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE, \App\Models\User::SELLER_ROLE]) && $request->viaRelationship == null;
                }),

            (new CopyOrderNumber())
                ->withoutConfirmation()
                ->showInline(),

        ];
    }

    /**
     * Build an "index" query for the given resource.
     */
    public static function indexQuery(NovaRequest $request, $query)
    {

        if ($request->user()  && $request->user()->role == \App\Models\User::SELLER_ROLE) {
            $query->where('user_id', $request->user()->parent_user_id ? $request->user()->parent_user_id : $request->user()->id);
        }

        return $query;
    }
}
