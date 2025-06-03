<?php

namespace App\Services;

use Exception;
use App\Models\GoogleSheet;
use Illuminate\Support\Facades\Log;
use Revolution\Google\Sheets\Facades\Sheets;

class GoogleSheetService
{
    protected $googleSheet;
    protected $sheetData;
    protected $parsedData;
    protected $ignoreEmptyFields = ['Sku', 'Order ID'];

    protected static $headerMappings = [
        'Order ID' => 'google_sheet_order_id',
        'Order date' => 'google_sheet_order_date',
        'Full name' => 'customer_name',
        'Region' => 'customer_city',
        'Country' => 'customer_address',
        'Phone' => 'customer_phone',
        'Product name' => 'product_name',
        'Product variant' => 'product_variant',
        'Total quantity' => 'quantity',
        'Total charge' => 'price',
        'Upsell' => 'is_upsell'
    ];

    public function __construct(GoogleSheet $googleSheet, $parseData = true)
    {
        $this->googleSheet = $googleSheet;
        $this->sheetData = self::fetchSheetData($this->googleSheet->sheet_id, $this->googleSheet->sheet_name);

        if ($parseData) {
            $this->parsedData = self::parseSheetData($this->sheetData, $googleSheet);
        }
    }

    // Fetch data from Google Sheet
    public static function fetchSheetData($sheetId, $sheetName, $range = null)
    {
        return Sheets::spreadsheet($sheetId)->sheet($sheetName)->get();
    }

    // Fetch all sheets from Google Sheets
    public static function fetchAllSheets($sheetId)
    {
        return Sheets::spreadsheet($sheetId)->get();
    }

    // Parse sheet data
    public static function parseSheetData($sheetData, $googleSheet)
    {
        $headers = $sheetData->pull(0);

        // Validate headers
        if (empty($headers) || !is_array($headers)) {
            throw new Exception("Invalid headers or headers are missing");
        }

        // Ensure headers are unique
        $headers = array_map('trim', $headers); 
        $uniqueHeaders = array_unique($headers);
        if (count($headers) !== count($uniqueHeaders)) {
            throw new Exception("Duplicate headers found");
        }

        $data = [];
        $mismatches = []; 

        foreach ($sheetData as $rowIndex => $row) {
            if (count($row) !== count($headers)) {
                $mismatches[] = [
                    'row' => $rowIndex,
                    'rowData' => $row,
                    'expectedColumns' => count($headers),
                    'actualColumns' => count($row)
                ];
                //continue;
            }

            // Make sure $row has the same number of elements as $headers
            $filledRow = array_pad($row, count($headers), '');

            // Combine headers and row
            $data[] = array_combine($headers, $filledRow);

        }

        if (!empty($mismatches)) {
            foreach ($mismatches as $mismatch) {
                $error = "ID: {$googleSheet->id} | Mismatch at row {$mismatch['row']}: expected {$mismatch['expectedColumns']} columns, found {$mismatch['actualColumns']}. Data: " . json_encode($mismatch['rowData']);
                // Log::channel('mismatching')->info('Success: ', [
                //     'date' => now(),
                //     'mismatch' => $error,
                // ]);
                error_log($error);
            }
        }

        

        return $data;
    }

    // Map headers to custom keys
    public static function mapHeaders($data, $sheet)
    {
        $mappedData = [];

        foreach ($data as $row) {
            $mappedRow = [];
            foreach ($row as $key => $value) {
                if (isset(self::$headerMappings[$key])) {
                    $mappedRow[self::$headerMappings[$key]] = $value;
                }

            }
            $mappedRow['items'][] = [
                'sku' => data_get($row, 'Sku', null),
                'product_variant' => data_get($row, 'Product variant', null),
                'product_name' => data_get($row, 'Product name', null),
                'quantity' => (int) data_get($row, 'Total quantity', null) ?? 0,
                'price' => (float) data_get($row, 'Total charge', null) ?? 0,
            ];
            $mappedRow['google_sheet_id'] = $sheet->id;
            $mappedData[] = $mappedRow;
        }

        return $mappedData;
    }

    public static function insertOrders()
    {
        // Implementation for inserting orders
    }
}
