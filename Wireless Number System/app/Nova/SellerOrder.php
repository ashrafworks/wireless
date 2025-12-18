<?php

namespace App\Nova;

use App\Nova\Metrics\TotalSellerOrder;
use App\Nova\Metrics\TotalSellerOrderUsd;
use App\Nova\Traits\OrderTrait;
use Eminiarts\Tabs\Tabs;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Number as NumberField;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Tabs\Tab;

class SellerOrder extends Resource
{
    use OrderTrait;
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Order>
     */
    public static $model = \App\Models\Order::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'reference';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'reference',
        'order_type',
        'status',
        'currency',
        'user.name',
    ];


    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            (new Tabs('Main Detials', [
                'Main Detials' => [
                    ...$this->commonFields($request),
                ]
            ]))->withToolbar(),

            new Tabs('Numbers', [
                'Numbers' => $this->numbers(),
            ]),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [
            TotalSellerOrder::make()
                ->width('1/2')
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE, \App\Models\User::SELLER_ROLE]);
                }),

            TotalSellerOrderUsd::make()
                ->width('1/2')
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE, \App\Models\User::SELLER_ROLE]);
                }),

        ];
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

    /**
     * Build an "index" query for the given resource.
     */
    public static function indexQuery(NovaRequest $request, $query)
    {

        $query->isSelling();

        if ($request->user()  && $request->user()->role == \App\Models\User::SELLER_ROLE) {
            $query->where('user_id', $request->user()->parent_user_id ? $request->user()->parent_user_id : $request->user()->id);
        }

        return $query;
    }
}
