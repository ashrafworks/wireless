<?php

namespace App\Nova\Traits;

use App\Nova\Buyer;
use App\Nova\Country;
use App\Nova\Seller;
use App\Nova\User;
use App\Nova\Wallet;
use App\Nova\WalletHistory;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

trait UserTrait
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\User::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function commonFields(NovaRequest $request, $extraFields = [])
    {
        return [
            BelongsTo::make('Parent User', 'parant', $request->resource instanceof User ? User::class : Seller::class)
                ->exceptOnForms()
                ->nullable()
                ->rules('nullable')
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                })
                ->filterable(),

            Text::make('Name')
                ->sortable()
                ->rules('required', 'max:255')
                ->exceptOnForms(),

            Text::make('First Name')
                ->required()
                ->rules('required', 'max:255')
                ->onlyOnForms(),

            Text::make('Last Name')
                ->nullable()
                ->rules('nullable', 'max:255')
                ->onlyOnForms(),

            Text::make('Email')
                ->sortable()
                ->rules('required', 'email', 'max:254')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}')
                ->copyable(),

            Password::make('Password')
                ->onlyOnForms()
                ->creationRules('required', Rules\Password::defaults())
                ->updateRules('nullable', Rules\Password::defaults()),


            BelongsTo::make('Country', 'country', Country::class)
                ->filterable()
                ->required()
                ->rules('required')
                ->sortable(),

            Text::make('Phone Number', 'phone_number')
                ->nullable()
                ->copyable()
                ->creationRules('nullable', 'numeric', 'regex:/(^[0-9+]{7,15}+$)/', 'unique:users,phone,NULL,id,deleted_at,NULL')
                ->updateRules('nullable', 'numeric', 'regex:/(^[0-9+]{7,15}+$)/', 'unique:users,phone,{{resourceId}},id,deleted_at,NULL')
                ->dependsOn('country', function ($field, NovaRequest $request, $formData) {

                    $viaResource = $request->viaResource;
                    $viaResourceId = $request->viaResourceId;
                    $viaRelationship = $request->viaRelationship;

                    $countryId = $viaResource === 'countries' ? $viaResourceId : (int) $formData->resource(Country::uriKey(), $formData->country);


                    if ($countryId) {
                        $phone_code = str_replace("+", "", \App\Models\Country::query()->find($countryId)->phone_code);
                        $max_digits = strlen($phone_code) + \App\Models\Country::query()->find($countryId)->phone_digits;
                        $field
                            ->show()
                            ->required()
                            ->help('Phone number must be unique with country code. example 973, 966, 971 ...')
                            ->default($phone_code)
                            ->creationRules('required', 'numeric', 'regex:/^(?:[0-9]{7,15})$/', 'unique:users,phone_number,NULL,id,deleted_at,NULL', 'min:' . $max_digits, 'max_digits:' . $max_digits)
                            ->updateRules('required', 'numeric', 'regex:/^(?:[0-9]{7,15})$/', 'unique:users,phone_number,{{resourceId}},id,deleted_at,NULL', 'min:' . $max_digits, 'max_digits:' . $max_digits)
                            ->copyable();

                        return;
                    }

                    $field
                        ->creationRules('required', 'numeric', 'regex:/^(?:[0-9]{7,15})$/', 'unique:users,phone_number,NULL,id,deleted_at,NULL')
                        ->updateRules('required', 'numeric', 'regex:/^(?:[0-9]{7,15})$/', 'unique:users,phone_number,{{resourceId}},id,deleted_at,NULL')
                        ->hide()
                        ->copyable()
                        ->help('Please write phone with country code without + sign. example 973, 966, 971 ...');
                }),

            Text::make('State')
                ->nullable()
                ->rules('nullable', 'max:255')
                ->hideFromIndex(),

            Text::make('Zip Code')
                ->nullable()
                ->rules('nullable', 'max:255')
                ->hideFromIndex(),

            Text::make('Address')
                ->nullable()
                ->rules('nullable', 'max:255')
                ->hideFromIndex(),


            Currency::make('Wallet', function () {
                return $this->wallet ? $this->wallet->available :  null;
            })
                ->nullable()
                ->symbol('USD')
                ->rules('nullable', 'max:255')
                ->exceptOnForms()
                ->canSee(function ($request) {
                    return  in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE])  && in_array($request->resource, [ 'buyers']);
                }),


            Boolean::make('Active', 'is_active')
                ->rules('nullable')
                ->default(true),

            Boolean::make('Admin', 'is_admin')
                ->rules('nullable')
                ->default(false)
                ->canSee(function ($request) {
                    return  in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                }),

            DateTime::make('Created At')
                ->displayUsing(function ($value) {
                    return $value ? $value->format('Y-m-d H:i') : null;
                })
                ->exceptOnForms(),

            ...$extraFields
        ];
    }

    public function wallet()
    {
        return [
            HasOne::make('Wallet', 'wallet', Wallet::class)
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return  in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]) && $this->parent_user_id == null && $this->role == \App\Models\User::USER_ROLE;
                }),
        ];
    }

    public function walletHistories()
    {
        return [
            HasMany::make('Wallet Histories', 'walletHistories', WalletHistory::class)
                ->onlyOnDetail()
                ->canSee(function ($request) {
                    return  in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]) && $this->parent_user_id == null && $this->role == \App\Models\User::USER_ROLE;
                }),
        ];
    }

    public function buyers()
    {
        return [
            HasMany::make('Buyers', 'users', Buyer::class)
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]) && $this->role == \App\Models\User::USER_ROLE;
                }),
        ];
    }

    public function sellers()
    {
        return [
            HasMany::make('Sellers', 'users', Seller::class)
                ->canSee(function ($request) {
                    return  in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]) && $this->role == \App\Models\User::SELLER_ROLE;
                }),
        ];
    }
}
