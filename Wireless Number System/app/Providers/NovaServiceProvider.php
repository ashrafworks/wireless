<?php

namespace App\Providers;

use App\Models\User as ModelsUser;
use App\Nova\Area;
use App\Nova\Buyer;
use App\Nova\Carrier;
use App\Nova\City;
use App\Nova\Country;
use App\Nova\Dashboards\Main;
use App\Nova\Number;
use App\Nova\Order;
use App\Nova\Seller;
use App\Nova\SellerOrder;
use App\Nova\Transaction;
use App\Nova\User;
use App\Nova\Wallet;
use App\Nova\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Fortify\Features;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaApplicationServiceProvider;

class NovaServiceProvider extends NovaApplicationServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Nova::mainMenu(function (Request $request) {

            return [

                MenuSection::dashboard(Main::class)
                    ->icon('chart-bar'),


                MenuSection::make('User Management', [

                    MenuItem::resource(User::class),

                    MenuItem::resource(Buyer::class),

                    MenuItem::resource(Seller::class)

                ])->icon('user')->collapsable()
                    ->canSee(function () {
                        return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                    }),

                MenuSection::resource(Buyer::class)
                    ->icon('user')
                    ->canSee(function () {
                        return (request()->user()->role == ModelsUser::USER_ROLE  && request()->user()->is_admin);
                    }),

                MenuSection::resource(Seller::class)
                    ->icon('user')
                    ->canSee(function () {
                        return (request()->user()->role == ModelsUser::SELLER_ROLE  && request()->user()->is_admin);
                    }),

                MenuSection::make('Request Management', [

                    MenuItem::resource(Carrier::class)
                        ->canSee(function () {
                            return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                        }),

                    MenuItem::resource(Number::class)->canSee(function () {
                        return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE, ModelsUser::SELLER_ROLE]);
                    }),

                ])->icon('collection')->collapsable(),

                MenuSection::make('Order Management', [

                    MenuItem::resource(Order::class)
                        ->withBadge(function () {
                            return  \App\Models\Order::query()
                                ->isPending()
                                ->isBuying()
                                ->count();
                        })
                        ->canSee(function () {
                            return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE, ModelsUser::USER_ROLE]);
                        }),
                    MenuItem::resource(SellerOrder::class)
                        ->canSee(function () {
                            return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE, ModelsUser::SELLER_ROLE]);
                        }),

                    MenuItem::resource(Transaction::class)
                        ->canSee(function () {
                            return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                        }),

                ])->icon('shopping-cart')->collapsable()
                    ->canSee(function () {
                        return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                    }),


                MenuSection::resource(Order::class)
                    ->withBadge(function () {
                        return  \App\Models\Order::query()
                            ->isPending()
                            ->isBuying()
                            ->count();
                    })
                    ->icon('shopping-cart')
                    ->canSee(function () {
                        return in_array(request()->user()->role, [ModelsUser::USER_ROLE]);
                    }),
                MenuSection::resource(SellerOrder::class)
                    ->icon('shopping-cart')
                    ->canSee(function () {
                        return in_array(request()->user()->role, [ModelsUser::SELLER_ROLE]);
                    }),

                MenuSection::resource(Transaction::class)
                    ->icon('shopping-cart')
                    ->canSee(function () {
                        return in_array(request()->user()->role, [ModelsUser::USER_ROLE]);
                    }),



                MenuSection::make('Settings', [

                    MenuItem::resource(Country::class)
                        ->canSee(function () {
                            return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                        }),

                    MenuItem::resource(City::class)
                        ->canSee(function () {
                            return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                        }),

                    MenuItem::resource(Wallet::class)
                        ->canSee(function () {
                            return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                        }),
                    MenuItem::resource(WalletHistory::class)
                        ->canSee(function () {
                            return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                        }),
                ])
                    ->collapsable()
                    ->icon('cog'),

                MenuSection::make('Logs')
                    ->path('/logs')
                    ->icon('document-duplicate')
                    ->canSee(function () {
                        return in_array(request()->user()->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
                    }),
            ];
        });
        parent::boot();
    }

    /**
     * Register the Nova routes.
     */
    protected function routes(): void
    {
        Nova::routes()

            ->withAuthenticationRoutes(default: true)
            // ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewNova', function (ModelsUser $user) {
            return $user && $user->is_active && in_array($user->role, [ModelsUser::SUPER_ADMINISTRATOR_ROLE, ModelsUser::SELLER_ROLE, ModelsUser::USER_ROLE, ModelsUser::NTS_ADMINISTRATOR_ROLE]);
        });
    }

    /**
     * Get the dashboards that should be listed in the Nova sidebar.
     *
     * @return array<int, \Laravel\Nova\Dashboard>
     */
    protected function dashboards(): array
    {
        return [
            new \App\Nova\Dashboards\Main,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array<int, \Laravel\Nova\Tool>
     */
    public function tools(): array
    {
        return [
            new \Stepanenko3\LogsTool\LogsTool
        ];
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        parent::register();

        //
    }
}
