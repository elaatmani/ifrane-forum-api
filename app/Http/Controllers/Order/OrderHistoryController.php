<?php

namespace App\Http\Controllers\Order;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderHistoryController extends Controller
{
    protected $repository;

    public function __construct(OrderRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $order = $this->repository->query()->with(['history', 'creator', 'google_sheet'])->where('id', $id)->first();

        $history = $order->history;

        // Get information about order creator
        $creatorInfo = null;
        
        if ($order->created_by) {
            $creator = $order->creator;
            $creatorInfo = [
                'id' => $creator ? $creator->id : null,
                'name' => $creator ? $creator->name : 'Unknown User',
                'source' => 'user'
            ];
        }
        
        // Check if order was created from Google Sheet
        $googleSheetInfo = null;
        
        if ($order->google_sheet_id) {
            $googleSheet = $order->google_sheet;
            $googleSheetInfo = [
                'id' => $googleSheet ? $googleSheet->id : null,
                'name' => $googleSheet ? $googleSheet->name : 'Unknown Sheet',
                'sheet_id' => $googleSheet ? $googleSheet->sheet_id : null,
                'sheet_name' => $googleSheet ? $googleSheet->sheet_name : null,
                'source' => 'google_sheet'
            ];
        }

        return response()->json([
            'message' => 'Order call updated',
            'code' => 'SUCCESS',
            'data' => $history,
            'creator' => $creatorInfo,
            'google_sheet' => $googleSheetInfo,
            'source' => $googleSheetInfo ? 'google_sheet' : ($creatorInfo ? 'user' : 'unknown')
        ]);
    }
}
