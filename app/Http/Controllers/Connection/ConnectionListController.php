<?php

namespace App\Http\Controllers\Connection;

use App\Http\Controllers\Controller;
use App\Http\Resources\Connection\ConnectionListResource;
use App\Repositories\Contracts\UserConnectionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ConnectionListController extends Controller
{
    protected $connectionRepository;

    public function __construct(UserConnectionRepositoryInterface $connectionRepository)
    {
        $this->connectionRepository = $connectionRepository;
    }

    /**
     * Get user connections with optional filtering.
     */
    public function __invoke(Request $request)
    {
        $userId = auth()->id();
        $perPage = $request->input('per_page', Config::get('connections.ui.pagination.default_per_page', 20));
        $maxPerPage = Config::get('connections.ui.pagination.max_per_page', 100);
        
        // Ensure per_page doesn't exceed maximum
        $perPage = min($perPage, $maxPerPage);

        // Get filter parameters
        $filters = $this->getFilters($request);

        // Determine which method to use based on query parameters
        if ($request->has('type')) {
            $type = $request->input('type');
            
            switch ($type) {
                case 'sent':
                    $connections = $this->connectionRepository->getSentRequests($userId, $filters['status'], $perPage);
                    break;
                case 'received':
                    $connections = $this->connectionRepository->getReceivedRequests($userId, $filters['status'], $perPage);
                    break;
                case 'established':
                    $connections = $this->connectionRepository->getUserConnections($userId, 'accepted', $perPage);
                    break;
                case 'pending':
                    $connections = $this->connectionRepository->getUserConnections($userId, 'pending', $perPage);
                    break;
                default:
                    $connections = $this->connectionRepository->getUserConnections($userId, $filters['status'], $perPage);
            }
        } else if (!empty($filters)) {
            $connections = $this->connectionRepository->getFilteredConnections($userId, $filters, $perPage);
        } else {
            $connections = $this->connectionRepository->getUserConnections($userId, null, $perPage);
        }

        // Transform the collection
        $connections->getCollection()->transform(function ($connection) {
            return new ConnectionListResource($connection);
        });

        // Add additional metadata
        $response = [
            'data' => $connections,
            'meta' => [
                'stats' => $this->connectionRepository->getConnectionStats($userId),
                'filters_applied' => $filters,
                // 'available_filters' => $this->getAvailableFilters(),
            ],
            'code' => 'SUCCESS'
        ];

        return response()->json($response, 200);
    }

    /**
     * Get filters from request.
     */
    private function getFilters(Request $request): array
    {
        $filters = [];

        // Status filter
        if ($request->has('status') && Config::get('connections.ui.filters.allow_status_filter', true)) {
            $filters['status'] = $request->input('status');
        }

        // Date range filters
        if (Config::get('connections.ui.filters.allow_date_filter', true)) {
            if ($request->has('from_date')) {
                $filters['from_date'] = $request->input('from_date');
            }
            if ($request->has('to_date')) {
                $filters['to_date'] = $request->input('to_date');
            }
        }

        // Search filter
        if ($request->has('search') && Config::get('connections.ui.filters.allow_search', true)) {
            $filters['search'] = $request->input('search');
        }

        return $filters;
    }

    /**
     * Get available filters configuration.
     */
    private function getAvailableFilters(): array
    {
        return [
            'status' => [
                'enabled' => Config::get('connections.ui.filters.allow_status_filter', true),
                'options' => array_keys(Config::get('connections.statuses', [])),
            ],
            'date_range' => [
                'enabled' => Config::get('connections.ui.filters.allow_date_filter', true),
            ],
            'search' => [
                'enabled' => Config::get('connections.ui.filters.allow_search', true),
            ],
            'type' => [
                'enabled' => true,
                'options' => ['sent', 'received', 'established', 'pending'],
            ],
        ];
    }
} 