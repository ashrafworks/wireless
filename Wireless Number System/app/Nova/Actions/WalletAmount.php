<?php

namespace App\Nova\Actions;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Notifications\NovaNotification;

class WalletAmount extends Action
{
    // use InteractsWithQueue;
    // use Queueable;

    protected $user;

    public function __construct($userId = null)
    {
        $this->user = User::find((int) $userId);
    }
    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $value = null;

        foreach ($models as $user) {


            if ($fields->amount) {
                $value = $fields->amount;
            }


            if ($user && $value) {

                $wallet = $user->wallet()
                    ->where('currency', 'USD')
                    ->first();

                $admin = Auth::user();

                if ($fields->type == WalletHistory::TYPE_DEBIT) {

                    $wallet->available = $wallet->available - $value;
                    $wallet->total = $wallet->total - $value;
                    $wallet->save();

                    WalletHistory::create([
                        'user_id' => $user->id,
                        'type' => WalletHistory::TYPE_DEBIT,
                        'amount' => $wallet->available,
                        'model_id' => null,
                        'model_type' => null,
                        'wallet_id' => $wallet->id,
                        'description' => "Debit by " . ($admin ?  $admin->name . " ( " . \App\Models\User::$rolesLables[$admin->role] . " )" : "admin"),
                        'currency' => "USD",
                        'status' => WalletHistory::STATUS_APPROVED
                    ]);

                    $user->notify(
                        NovaNotification::make()
                            ->message('Your wallet has been debited by ' . ($admin ?  $admin->name . " ( " . \App\Models\User::$rolesLables[$admin->role] . " )" : "admin") . ' for ' . $value . ' USD')
                            ->type('success')
                    );
                } elseif ($fields->type == WalletHistory::TYPE_CREDIT) {

                    $wallet->available = $wallet->available + $value;
                    $wallet->total = $wallet->total + $value;
                    $wallet->save();

                    Transaction::create([
                        'user_id' => $user->id,
                        'wallet_id' => $wallet->id,
                        'charged_price' => $wallet->available,
                        'origin' => Transaction::WALLET,
                        'platform' => Transaction::ADMIN_DASHBOARD,
                        'currency' => Transaction::USD,
                        'status' => Transaction::COMPLETED
                    ]);

                    WalletHistory::create([
                        'user_id' => $user->id,
                        'type' => WalletHistory::TYPE_CREDIT,
                        'amount' => $wallet->available,
                        'model_id' => null,
                        'model_type' => null,
                        'wallet_id' => $wallet->id,
                        'description' => "Top-up by " . ($admin ?  $admin->name . " ( " . \App\Models\User::$rolesLables[$admin->role] . " )" : "admin"),
                        'currency' => "USD",
                        'status' => WalletHistory::STATUS_APPROVED
                    ]);

                    $user->notify(
                        NovaNotification::make()
                            ->message('Your wallet has been credited by ' . ($admin ?  $admin->name . " ( " . \App\Models\User::$rolesLables[$admin->role] . " )" : "admin") . ' for ' . $value . ' USD')
                            ->type('success')
                            ->icon('success')
                    );
                }
            }
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {

        return [
            Currency::make('Amount', 'amount')
                ->step(0.01)
                ->default(0)
                ->symbol('USD')
                ->required()
                ->rules('required', 'numeric'),

            Select::make('Type', 'type')
                ->default(WalletHistory::TYPE_CREDIT)
                ->options([
                    WalletHistory::TYPE_DEBIT => 'Charged',
                    WalletHistory::TYPE_CREDIT => 'Credit',
                ])
                ->displayUsingLabels()
                ->rules('required')
                ->required()
        ];
    }
}
