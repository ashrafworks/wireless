<?php

namespace App\Nova;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Line;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Stack;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Transaction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Transaction>
     */
    public static $model = \App\Models\Transaction::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public function title()
    {
        return optional($this)->user ? $this->user->name . ' ( ' . $this->charged_price . $this->currency . ' - ' . $this->status . ' )' : $this->id;
    }

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'order.reference',
        'order.user.name',
        'user.name',
        'user.email',
        'user.phone_number',
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

            Stack::make('Order \ Created At', 'order_id', [

                BelongsTo::make('Order', 'order', Order::class)
                    ->required()
                    ->rules('required')
                    ->onlyOnIndex(),

                Line::make('Created At', 'created_at')
                    ->extraClasses('italic font-medium text-80')
                    ->asSmall()
                    ->displayUsing(function ($created_at) {
                        return $created_at->format('Y-m-d H:i A');
                    })
                    ->onlyOnIndex(),
            ])
                ->onlyOnIndex()
                ->sortable(),

            BelongsTo::make('Order', 'order', Order::class)
                ->sortable()
                ->searchable()
                ->required()
                ->rules(['required'])
                ->showOnPreview()
                ->hideFromIndex(),

            BelongsTo::make('User', 'user', User::class)
                ->sortable()
                ->searchable()
                ->required()
                ->rules(['required'])
                ->showOnPreview()
                ->hideFromIndex(),

            BelongsTo::make('Wallet', 'wallet', Wallet::class)
                ->sortable()
                ->searchable()
                ->nullable()
                ->rules(['nullable'])
                ->showOnPreview()
                ->hideFromIndex(),

            Stack::make('Platform \ Status', 'platform', [

                Line::make('Platform', 'platform')
                    ->extraClasses('italic font-medium text-80')
                    ->asSmall()
                    ->displayUsing(function ($platform) {
                        return 'Platform: ' . ucwords(strtolower(str_replace('_', ' ', $platform)));
                    })
                    ->onlyOnIndex(),

                Line::make('Status', 'status')
                    ->extraClasses('italic font-medium text-80')
                    ->asSmall()
                    ->displayUsing(function ($status) {
                        return 'Status: ' . ucwords(strtolower(str_replace('_', ' ', $status)));
                    })
                    ->onlyOnIndex(),

            ])
                ->onlyOnIndex()
                ->sortable(),


            Select::make('Platform', 'platform')
                ->options([
                    \App\Models\Transaction::WEB => 'Web',
                    \App\Models\Transaction::MOBILE => 'Mobile',
                    \App\Models\Transaction::ADMIN_DASHBOARD => 'Admin',
                ])
                ->default(\App\Models\Transaction::ADMIN_DASHBOARD)
                ->rules(['required'])
                ->required()
                ->displayUsingLabels()
                ->filterable()
                ->showOnPreview()
                ->hideFromIndex(),


            // BHD
            Text::make('Currency', 'currency')
                ->onlyOnDetail(),

            Currency::make('Total', 'charged_price')
                ->symbol(\App\Models\Transaction::USD)
                ->context(new \Brick\Money\Context\CustomContext(3))
                ->step(0.00001)
                ->required(),

            Select::make('Origin')
                ->options(\App\Models\Transaction::GET_ORIGIN())
                ->rules(['required'])
                ->required()
                ->displayUsingLabels(),

            Select::make('Status', 'status')
                ->sortable()
                ->rules(['required'])
                ->required()
                ->options(\App\Models\Transaction::GET_STATUS())
                ->displayUsingLabels()
                ->filterable(),

            DateTime::make('Created at', 'created_at')
                ->exceptOnForms()
                ->sortable()
                ->displayUsing(function ($created_at) {
                    return $created_at->format('Y-m-d g:i:s A');
                })
                ->hideFromIndex(),
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

    /**
     * Build an "index" query for the given resource.
     */
    public static function indexQuery(NovaRequest $request, $query)
    {

        if ($request->user()  && $request->user()->role == \App\Models\User::USER_ROLE) {
            $query->where('user_id', $request->user()->parent_user_id ? $request->user()->parent_user_id : $request->user()->id);
        }

        return $query;
    }
}
