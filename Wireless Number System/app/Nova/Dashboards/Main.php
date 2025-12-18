<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics\Buyer;
use App\Nova\Metrics\NumberPerDay;
use App\Nova\Metrics\Seller;
use App\Nova\Metrics\TotalNumber;
use App\Nova\Metrics\TotalOrder;
use App\Nova\Metrics\TotalOrderUsd;
use App\Nova\Metrics\TotalPendingBuyerOrder;
use App\Nova\Metrics\TotalSellerOrder;
use App\Nova\Metrics\TotalSellerOrderUsd;
use App\Nova\Metrics\TotalUnusedNumber;
use App\Nova\Metrics\TotalUsedNumber;
use App\Nova\Metrics\UserPerCountry;
use App\Nova\Metrics\UserPerDay;
use App\Nova\Metrics\WalletBalance;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array
     */
    public function cards()
    {
        return [
            TotalOrderUsd::make()
                // ->width('1/2')
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE, \App\Models\User::USER_ROLE]);
                }),

            TotalOrder::make()
                // ->width('1/2')
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE, \App\Models\User::USER_ROLE]);
                }),

            TotalPendingBuyerOrder::make()
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE, \App\Models\User::USER_ROLE]);
                }),

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

            Buyer::make()
                ->width('1/2')
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]) || ($request->user()->role == \App\Models\User::USER_ROLE  && $request->user()->is_admin);
                }),

            Seller::make()
                ->width('1/2')
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]) || ($request->user()->role == \App\Models\User::SELLER_ROLE && $request->user()->is_admin);
                }),

            WalletBalance::make()
                ->width('1/2')
                ->canSee(function ($request) {
                    return $request->user()->role == \App\Models\User::USER_ROLE && !$request->user()->parent_user_id;
                }),

            UserPerCountry::make()
                ->width('2/3')
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                }),

            UserPerDay::make()
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                }),

            TotalNumber::make()
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE, \App\Models\User::SELLER_ROLE]);
                }),

            TotalUsedNumber::make()
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                }),

            NumberPerDay::make()
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE, \App\Models\User::SELLER_ROLE]);
                }),
        ];
    }
}
