<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NumberImport implements ToCollection, WithHeadingRow
{
    protected $records;

    public function __construct()
    {
        $this->records = collect();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Check if all fields are empty
            if (
                empty($row['number']) &&
                empty($row['account_number']) &&
                empty($row['transfer_pin'])
            ) {
                continue; // Skip this row if all fields are empty
            }
            $this->records->push([
                'number' => $row['number'] ?? null,
                'account_number' => $row['account_number'] ?? null,
                'pin' => $row['transfer_pin'] ?? null,
            ]);
        }
    }

    public function getExtractedRecords()
    {
        return $this->records;
    }
}
