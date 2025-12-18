<?php

namespace App\Nova;

use App\Nova\Actions\WalletAmount;
use App\Nova\Traits\UserTrait;
use Eminiarts\Tabs\Tabs;
use Eminiarts\Tabs\Traits\HasTabs;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Auth\PasswordValidationRules;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Tabs\TabsGroup;


class Buyer extends Resource
{
    use UserTrait, HasTabs;
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\User>
     */
    public static $model = \App\Models\User::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
        'first_name',
        'last_name',
        'phone_number',
        'email'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [

            (new Tabs('Main Details', [
                'Main Details' => [
                    ...$this->commonFields($request, [
                        Hidden::make('Role')
                            ->default(\App\Models\User::USER_ROLE),

                    
                    ]),

                ],
                'Wallet' => $this->wallet(),
            ]))->withToolbar(),

            (new Tabs('Additional Details', [
                'Buyer Users' => $this->buyers(),
                'Wallet Histories' => $this->walletHistories(),
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
        return [

            (new WalletAmount($request->resourceId))
                ->showInline()
                ->canSee(function () use ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                })
        ];
    }


    /**
     * Build an "index" query for the given resource.
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        if ($request->user()  && $request->user()->role == \App\Models\User::USER_ROLE && $request->user()->isAdmin()) {
            $query->where('parent_user_id', $request->user()->id);
        }


        return $query->where('role', \App\Models\User::USER_ROLE);
    }
}
