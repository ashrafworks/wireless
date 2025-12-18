<?php

namespace App\Nova\Actions;

use App\Imports\NumberImport;
use App\Jobs\AutoAssignNumbers;
use App\Jobs\ProcessNumberImport;
use App\Mail\NewNumberAdded;
use App\Models\Number;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text as FieldsText;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Notifications\NovaNotification;
use Laravel\Nova\URL as NovaURL;
use Maatwebsite\Excel\Facades\Excel;

class AddNumber extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if (!$fields->file) {
            return Action::danger('No file uploaded.');
        }

        // Store the uploaded file in 'storage/app/uploads/'
        $path = $fields->file->store('uploads');

        // Check if file exists before processing
        if (!Storage::exists($path)) {
            return Action::danger("Uploaded file not found: $path");
        }

        // Get full path for import
        $fullPath = Storage::path($path);

        // Process the file using Laravel Excel Import class
        $import = new NumberImport();
        Excel::import($import, $fullPath);

        // Get the extracted records
        $records = $import->getExtractedRecords();

        // Delete the temporary file
        Storage::delete($path);

        if ($records->isEmpty()) {
            return Action::danger('No data extracted from the file.');
        }

        $userId = $fields->seller;
        $authUser = Auth::user();

        if ($authUser->role == User::SELLER_ROLE) {
            if (!$authUser->parent_user_id) {
                $userId = $authUser->id;
            } elseif ($authUser->parent_user_id) {
                $userId = $authUser->parent_user_id;
            }
        }

        // Check if any numbers already exist
        $isAnyNumberExists = Number::query()
            ->whereIn('phone_number', $records->pluck('number'))
            ->exists();

        if ($isAnyNumberExists) {
            return Action::danger('Number already exists.');
        }

        // Extract serializable fields
        $fieldData = [
            'seller' => (int)$fields->seller,
            'carrier' => (int) $fields->carrier,
            'city' => (int)$fields->city,
            'price' => (double) $fields->price,
            'expiry' => $fields->expiry,
        ];

        // Dispatch the job with serializable data
        ProcessNumberImport::dispatch($records, $fieldData, (int) $userId, $authUser);

        return Action::message('Numbers added successfully.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Select::make('Seller', 'seller')
                ->options(\App\Models\User::isSellerRole()->isActive()->pluck('name', 'id'))
                ->rules('required')
                ->required()
                ->displayUsingLabels()
                ->searchable()
                ->fullWidth()
                ->canSee(function ($request) {
                    return in_array($request->user()->role, [\App\Models\User::SUPER_ADMINISTRATOR_ROLE, \App\Models\User::NTS_ADMINISTRATOR_ROLE]);
                }),

            Select::make('Carrier', 'carrier')
                ->options(\App\Models\Carrier::isActive()->pluck('name', 'id'))
                ->rules('required')
                ->required()
                ->displayUsingLabels()
                ->searchable()
                ->fullWidth(),

            Select::make('City', 'city')
                ->options(\App\Models\City::isActive()->pluck('name', 'id'))
                ->rules('required')
                ->required()
                ->displayUsingLabels()
                ->searchable()
                ->fullWidth(),

            File::make('Excel', 'file')
                ->acceptedTypes('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->rules('required', 'max:10240')
                ->required()
                ->fullWidth(),

            Currency::make('Price', 'price')
                ->step(0.01)
                ->symbol('USD')
                ->required()
                ->rules('required', 'numeric')
                ->fullWidth(),

            FieldsText::make('Expiry', 'expiry')
                ->rules('required')
                ->required()
                ->fullWidth()
                ->withMeta(['type' => 'date']),
        ];
    }
}
