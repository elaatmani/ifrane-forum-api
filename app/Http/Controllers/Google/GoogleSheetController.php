<?php

namespace App\Http\Controllers\Google;

use App\Models\GoogleSheet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\GoogleSheetService;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class GoogleSheetController extends Controller
{
    protected $orderRepository;
    protected $productRepository;

    public function __construct(OrderRepositoryInterface $orderRepository, ProductRepositoryInterface $productRepository)
    {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
    }

    public function sync()
    {
        // Authenticate as user ID 32 which is system user
        Auth::loginUsingId(32);
    
        $sheets = GoogleSheet::active()->get();
        $response = null;
    
        foreach ($sheets as $sheet) {
            try {
                $response = $this->syncSheet($sheet->id);
    
                Log::channel('sheets_sync')->info('Success: ', [
                    'date' => now(),
                    'response' => $response,
                ]);
            } catch (\Throwable $th) {
                Log::channel('sheets_sync_errors')->info('Failed: ', [
                    'date' => now(),
                    'sheet_id' => $sheet->id,
                    'error' => $th->getMessage(),
                    'trace' => $th->getTrace()
                ]);
            }
        }
    
        // Log out before sending the response
        Auth::logout();

        return response()->json([
            'results' => $response
        ]);
    }

    public function syncSheet($id = 1)
    {
        $googleSheet = GoogleSheet::where('id', $id)->first();
        $orders = $this->fetchAndParseOrders($googleSheet);

        // Use an associative array to group orders by google_sheet_order_id
        $mergedOrders = [];

        foreach ($orders as $order) {
            if (!isset($order['google_sheet_order_id'])) {
                Log::channel('mismatching')->info('Success: ', [
                    'date' => now(),
                    'error' => "Not Found ID",
                    'mismatch' => $order,
                ]);
                continue;
            }
            $googleSheetOrderId = $order['google_sheet_order_id'];

            if (isset($mergedOrders[$googleSheetOrderId])) {
                // Merge the items of orders with the same google_sheet_order_id
                $mergedOrders[$googleSheetOrderId]['items'] = array_merge(
                    $mergedOrders[$googleSheetOrderId]['items'],
                    $order['items']
                );
            } else {
                // If it's the first time encountering this id, initialize the order
                $mergedOrders[$googleSheetOrderId] = $order;
            }
        }

        // Convert back to an indexed array if needed
        $mergedOrders = array_values($mergedOrders);

        $orders = $mergedOrders;


        $existingOrderCombinations = $this->fetchExistingOrderCombinations();

        $newOrders = [];
        $ordersWithError = [];

        foreach ($orders as $order) {
            if ($this->orderExists($order, $existingOrderCombinations)) {
                continue;
            }

            if ($this->hasErrors($order)) {
                $ordersWithError[] = $order;
            } else {
                $newOrders[] = $order;
            }
        }

        if (true) {
            $this->saveOrders($newOrders);
        }
        $googleSheet->orders_with_errors_count = count($ordersWithError);
        $googleSheet->last_synced_at = now();
        $googleSheet->save();

        return $this->createResponse($newOrders, $ordersWithError);
    }

    public function saveOrders($orders)
    {
        $saved = [];

        foreach ($orders as $o) {
            $items = $this->formatItems(data_get($o, 'items'));
            $order = $this->orderRepository->create($o, $items);
            $saved[] = $order;
        }

        return $saved;
    }


    public function formatItems($items)
    {
        // dd($items);
        $formattedItems = [];
        foreach ($items as $item) {
            $product = $this->productRepository->query()->where('sku', $item['sku'])->first();

            if (!$product) {
                continue;
            }

            $item['product_id'] = $product->id;

            if ($product && $product->variants()->count() > 0) {
                $item['product_variant_id'] = $product->variants()->first()?->id;
            } else {
                $item['product_variant_id'] = null;
            }


            $formattedItems[] = [
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ];
        }

        return $formattedItems;
    }

    protected function fetchAndParseOrders($googleSheet)
    {
        $sheetData = GoogleSheetService::fetchSheetData($googleSheet->sheet_id, $googleSheet->sheet_name);
        $parsedSheetData = GoogleSheetService::parseSheetData($sheetData, $googleSheet);
        return GoogleSheetService::mapHeaders($parsedSheetData, $googleSheet);
    }

    protected function fetchExistingOrderCombinations()
    {
        $existingOrders = $this->orderRepository->query()
            ->select('google_sheet_id', 'google_sheet_order_id')
            ->get()
            ->toArray();

        return array_map(function ($order) {
            return $order['google_sheet_id'] . '_' . $order['google_sheet_order_id'];
        }, $existingOrders);
    }

    protected function orderExists($order, $existingOrderCombinations)
    {
        $googleSheetId = data_get($order, 'google_sheet_id');
        $googleSheetOrderId = data_get($order, 'google_sheet_order_id');

        return in_array($googleSheetId . '_' . $googleSheetOrderId, $existingOrderCombinations);
    }

    protected function hasErrors(&$order)
    {
        $errors = [];
        $order['errors'] = [];
        foreach ($order['items'] as $item) {
            $sku = data_get($item, 'sku'); // Access the SKU of each item
            if (!$sku) {
                $errors[] = 'Missing SKU for item: ' . data_get($item, 'product_name');
            } elseif (!$this->productExists($sku)) {
                $errors[] = 'Invalid SKU: ' . $sku;
            }
        }
        

        if (!data_get($order, 'google_sheet_order_id')) {
            $errors[] = 'Missing Order ID';
        }

        if (!empty($errors)) {
            $order['errors'] = $errors;
            return true;
        }

        return false;
    }

    protected function productExists($sku)
    {
        return $this->productRepository->query()->where('sku', $sku)->exists();
    }

    protected function createResponse($newOrders, $ordersWithError)
    {
        return response()->json([
            'data' => [
                'new_orders' => $newOrders,
                'orders_with_errors' => $ordersWithError,
            ],
            'code' => 'SUCCESS',
        ]);
    }
}
