<?php

namespace App\Nova\Actions;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class CopyOrderNumber extends Action
{
    use InteractsWithQueue, Queueable;

    
    public function name()
    {
        return 'Copy Numbers';
    }

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */

    public function handle(ActionFields $fields, Collection $models)
    {
        // Initialize the data array with a temporary header
        $data = ["Phone Number     PIN     Account Number    Expiry Date"];

        // Initialize arrays to track maximum lengths for each column
        $max_phone_length = strlen("Phone Number");
        $max_pin_length = strlen("PIN"); // Will adjust for "PIN: " prefix
        $max_account_length = strlen("Account Number"); // Will adjust for "#" prefix
        $max_expiry_length = strlen("Expiry Date");

        // First pass: Collect data and determine maximum lengths
        $rows = [];

        foreach ($models as $model) {
            if ($model instanceof \App\Models\Order) {
                if ($model->numbers) {
                    $numbers = $model->numbers->map(function ($number) use (&$max_phone_length, &$max_pin_length, &$max_account_length, &$max_expiry_length, &$rows) {
                        // Include prefixes in length calculations
                        $pin_with_prefix =  $number->pin;
                        $account_with_prefix = $number->account_number;
                        $expiry_with_formated = ($number->expiry ? Carbon::parse($number->expiry)->format('d/m/Y') : '');

                        // Update maximum lengths
                        $max_phone_length = max($max_phone_length, strlen($number->phone_number));
                        $max_pin_length = max($max_pin_length, strlen($pin_with_prefix));
                        $max_account_length = max($max_account_length, strlen($account_with_prefix));
                        $max_expiry_length = max($max_expiry_length, strlen($expiry_with_formated));

                        $rows[] = [
                            'phone_number' => $number->phone_number,
                            'pin' => $pin_with_prefix,
                            'account_number' => $account_with_prefix,
                            'expiry' => $expiry_with_formated
                        ];
                    })->toArray();
                    $rows = array_merge($rows, $numbers); // Collect rows
                }
            } elseif ($model instanceof \App\Models\Number) {

                // Include prefixes in length calculations
                $pin_with_prefix =  $model->pin;
                $account_with_prefix = $model->account_number;
                $expiry_with_formated = ($model->expiry ? Carbon::parse($model->expiry)->format('d/m/Y') : '');
                $max_phone_length = max($max_phone_length, strlen($model->phone_number));
                $max_pin_length = max($max_pin_length, strlen($pin_with_prefix));
                $max_account_length = max($max_account_length, strlen($account_with_prefix));
                $max_expiry_length = max($max_expiry_length, strlen($expiry_with_formated));

                $rows[] = [
                    'phone_number' => $model->phone_number,
                    'pin' => $model->pin,
                    'account_number' => $model->account_number,
                    'expiry' => $expiry_with_formated
                ];
            }
        }

        // Check if any data was collected (excluding the header)
        if (empty($rows)) {
            return Action::danger('No numbers found');
        }

        // Second pass: Format each row with padding
        $data = [
            str_pad("Phone Number", $max_phone_length, " ") . "  " .
                str_pad("PIN", $max_pin_length, " ") . "  " .
                str_pad("Account Number", $max_account_length, " ") . "  " .
                str_pad("Expiry Date", $max_expiry_length, " ")
        ]; // Reformat header
        foreach ($rows as $row) {
            if(empty($row['phone_number']) && empty($row['pin']) && empty($row['account_number']) && empty($row['expiry'])){
                continue;
            }
            $formatted = str_pad($row['phone_number'], $max_phone_length, " ") . "  " .
                str_pad($row['pin'], $max_pin_length, " ") . "  " .
                str_pad($row['account_number'], $max_account_length, " ") . "  " .
                $row['expiry']; // Apply padding to account_number
            $data[] = $formatted;
        }


        return ActionResponse::modal('copyable-modal', [
            'title' => 'Copy Numbers',
            'value' => $data, // Pass formatted text for copying
            'debug' => true, // Keep for debugging
        ]);
    }
    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
