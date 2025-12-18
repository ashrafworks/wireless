<?php

namespace App\Nova\Actions;

use App\Exports\ApplicationExport;
use App\Exports\NumberExport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportOrderNumbers extends Action
{
    use InteractsWithQueue, Queueable;

    protected $ids = [];

    protected $fileName;

    public function __construct()
    {
        $this->fileName = 'number-exports-' . Carbon::today()->toDateString() . ".csv";
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

        foreach ($models as $order) {
            $this->ids = $order->numbers->pluck('id')->toArray();
        }
        return null;
    }

    public function handleResult(ActionFields $fields, $results)
    {
        if (!count($this->ids)) {
            return Action::danger('No numbers found');
        }
        
        $numbers = \App\Models\Number::query()->whereIn('id', $this->ids)->get();
        $data = [];



        foreach ($numbers as $number) {
            $data[] = [
                'Phone Number' =>  $number->phone_number,
                'Account Number' => $number->account_number,
                'Pin' =>   $number->pin,
                'Expired At'     => $number->expiry ? Carbon::parse($number->expiry)->format('d/m/Y') : '',
            ];
        }

        if (!count($data)) {
            return Action::danger('No numbers found');
        }

        $response = Excel::download(
            new NumberExport(collect($data)),
            $this->fileName
        );


        if (!$response instanceof BinaryFileResponse || $response->isInvalid()) {
            return Action::danger(__('Resource could not be exported.'));
        }
        return Action::download($this->getDownloadUrl($response), $this->fileName);
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

    /**
     * @param BinaryFileResponse $response
     *
     * @return string
     */
    protected function getDownloadUrl(BinaryFileResponse $response): string
    {
        return URL::temporarySignedRoute('laravel-nova-excel.download', now()->addMinutes(1), [
            'path' => encrypt($response->getFile()->getPathname()),
            'filename' => $this->fileName,
        ]);
    }
}
