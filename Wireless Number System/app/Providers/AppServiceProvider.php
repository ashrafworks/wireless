<?php

namespace App\Providers;

use App\Models\Area;
use App\Models\Carrier;
use App\Models\Number;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Observers\AreaObserver;
use App\Observers\CarrierObserver;
use App\Observers\NumberObserver;
use App\Observers\OrderObserver;
use App\Observers\TransactionObserver;
use App\Observers\UserObserver;
use App\Observers\WalletHistoryObserver;
use App\Observers\WalletObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Nova::footer(function ($request) {
            return Blade::render('<p class="mt-8 text-center text-xs text-80"><span class="px-1">&middot;</span>Made with <svg xmlns="http://www.w3.org/2000/svg" style="width: 12px; color: rgb(228,0,0); display: inline;" viewBox="0 0 20 20"fill="currentColor"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" /></svg> by Webbulls.us<span class="px-1">&middot;</span></p>');
        });

        User::observe(UserObserver::class);
        Wallet::observe(WalletObserver::class);
        WalletHistory::observe(WalletHistoryObserver::class);
        Area::observe(AreaObserver::class);
        Number::observe(NumberObserver::class);
        Carrier::observe(CarrierObserver::class);
        Transaction::observe(TransactionObserver::class);
        Order::observe(OrderObserver::class);
    }
}
