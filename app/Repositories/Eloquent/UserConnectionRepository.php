<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\UserConnection;
use App\Repositories\Contracts\UserConnectionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Exception;

class UserConnectionRepository extends BaseRepository implements UserConnectionRepositoryInterface
{
    public function __construct(UserConnection $model)
    {
        parent::__construct($model);
    }

    /**
     * Send a connection request from one user to another.
     */
    public function sendConnectionRequest(int $senderId, int $receiverId, string $message): ?UserConnection
    {
        DB::beginTransaction();

        try {
            // Check if request can be sent
            $canSend = $this->canSendConnectionRequest($senderId, $receiverId);
            if (!$canSend['can_send']) {
                throw new Exception($canSend['reason']);
            }

            // Create the connection request
            $connection = $this->model->create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $message,
                'status' => UserConnection::STATUS_PENDING,
            ]);

            DB::commit();
            return $connection;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Accept a connection request.
     */
    public function acceptConnectionRequest(int $connectionId, int $userId): ?UserConnection
    {
        DB::beginTransaction();

        try {
            $connection = $this->model->where('id', $connectionId)
                                     ->where('receiver_id', $userId)
                                     ->where('status', UserConnection::STATUS_PENDING)
                                     ->first();

            if (!$connection) {
                throw new Exception('Connection request not found or cannot be accepted');
            }

            $connection->accept();

            DB::commit();
            return $connection;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Decline a connection request.
     */
    public function declineConnectionRequest(int $connectionId, int $userId): ?UserConnection
    {
        DB::beginTransaction();

        try {
            $connection = $this->model->where('id', $connectionId)
                                     ->where('receiver_id', $userId)
                                     ->where('status', UserConnection::STATUS_PENDING)
                                     ->first();

            if (!$connection) {
                throw new Exception('Connection request not found or cannot be declined');
            }

            $connection->decline();

            DB::commit();
            return $connection;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Cancel a connection request.
     */
    public function cancelConnectionRequest(int $connectionId, int $userId): ?UserConnection
    {
        DB::beginTransaction();

        try {
            $connection = $this->model->where('id', $connectionId)
                                     ->where('sender_id', $userId)
                                     ->where('status', UserConnection::STATUS_PENDING)
                                     ->first();

            if (!$connection) {
                throw new Exception('Connection request not found or cannot be cancelled');
            }

            $connection->cancel();

            DB::commit();
            return $connection;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Remove/Block a connection.
     */
    public function removeConnection(int $connectionId, int $userId): bool
    {
        DB::beginTransaction();

        try {
            $connection = $this->model->where('id', $connectionId)
                                     ->where(function($query) use ($userId) {
                                         $query->where('sender_id', $userId)
                                               ->orWhere('receiver_id', $userId);
                                     })
                                     ->where('status', UserConnection::STATUS_ACCEPTED)
                                     ->first();

            if (!$connection) {
                throw new Exception('Connection not found or cannot be removed');
            }

            $connection->delete(); // Soft delete

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get all connections for a user with optional status filter.
     */
    public function getUserConnections(int $userId, ?string $status = null, int $perPage = 20)
    {
        $query = $this->model->forUser($userId)
                            ->with(['sender', 'receiver'])
                            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get connection requests received by a user.
     */
    public function getReceivedRequests(int $userId, ?string $status = null, int $perPage = 20)
    {
        $query = $this->model->receivedBy($userId)
                            ->with(['sender'])
                            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get connection requests sent by a user.
     */
    public function getSentRequests(int $userId, ?string $status = null, int $perPage = 20)
    {
        $query = $this->model->sentBy($userId)
                            ->with(['receiver'])
                            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Check if a connection exists between two users.
     */
    public function connectionExists(int $userId1, int $userId2, ?string $status = null): bool
    {
        $query = $this->model->betweenUsers($userId1, $userId2);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->exists();
    }

    /**
     * Get connection between two users.
     */
    public function getConnectionBetweenUsers(int $userId1, int $userId2): ?UserConnection
    {
        return $this->model->betweenUsers($userId1, $userId2)
                          ->with(['sender', 'receiver'])
                          ->first();
    }

    /**
     * Get mutual connections between two users.
     */
    public function getMutualConnections(int $userId1, int $userId2)
    {
        $user1Connections = $this->model->forUser($userId1)
                                       ->accepted()
                                       ->get()
                                       ->map(function($connection) use ($userId1) {
                                           return $connection->getOtherUser($userId1)->id;
                                       });

        $user2Connections = $this->model->forUser($userId2)
                                       ->accepted()
                                       ->get()
                                       ->map(function($connection) use ($userId2) {
                                           return $connection->getOtherUser($userId2)->id;
                                       });

        $mutualIds = $user1Connections->intersect($user2Connections);

        return User::whereIn('id', $mutualIds)->get();
    }

    /**
     * Get connection statistics for a user.
     */
    public function getConnectionStats(int $userId): array
    {
        $stats = [
            'total_connections' => $this->model->forUser($userId)->accepted()->count(),
            'pending_sent' => $this->model->sentBy($userId)->pending()->count(),
            'pending_received' => $this->model->receivedBy($userId)->pending()->count(),
            'total_requests_sent' => $this->model->sentBy($userId)->count(),
            'total_requests_received' => $this->model->receivedBy($userId)->count(),
            'declined_requests' => $this->model->forUser($userId)->declined()->count(),
            'cancelled_requests' => $this->model->forUser($userId)->cancelled()->count(),
        ];

        return $stats;
    }

    /**
     * Check if user can send connection request to another user.
     */
    public function canSendConnectionRequest(int $senderId, int $receiverId): array
    {
        // Check if trying to connect to self
        if ($senderId === $receiverId) {
            if (!Config::get('connections.rules.allow_self_connection', false)) {
                return ['can_send' => false, 'reason' => 'Cannot send connection request to yourself'];
            }
        }

        // Check if receiver exists
        if (!User::find($receiverId)) {
            return ['can_send' => false, 'reason' => 'Target user not found'];
        }

        // Check if connection already exists
        if ($this->connectionExists($senderId, $receiverId)) {
            if (!Config::get('connections.rules.allow_duplicate_requests', false)) {
                return ['can_send' => false, 'reason' => 'Connection request already exists'];
            }
        }

        // Check if there's a recent declined request (cooldown period)
        $cooldownDays = Config::get('connections.rate_limits.cooldown_after_decline', 7);
        if ($cooldownDays > 0) {
            $recentDeclined = $this->model->betweenUsers($senderId, $receiverId)
                                         ->declined()
                                         ->where('responded_at', '>=', now()->subDays($cooldownDays))
                                         ->exists();
            
            if ($recentDeclined) {
                return ['can_send' => false, 'reason' => "Must wait {$cooldownDays} days after declined request"];
            }
        }

        // Check rate limits
        if (Config::get('connections.rate_limits.enabled', true)) {
            $maxPerHour = Config::get('connections.rate_limits.max_requests_per_hour', 10);
            $requestsInLastHour = $this->model->sentBy($senderId)
                                             ->where('created_at', '>=', now()->subHour())
                                             ->count();

            if ($requestsInLastHour >= $maxPerHour) {
                return ['can_send' => false, 'reason' => 'Rate limit exceeded: too many requests in the last hour'];
            }

            $maxPerDay = Config::get('connections.rate_limits.max_requests_per_day', 50);
            $requestsInLastDay = $this->model->sentBy($senderId)
                                            ->where('created_at', '>=', now()->subDay())
                                            ->count();

            if ($requestsInLastDay >= $maxPerDay) {
                return ['can_send' => false, 'reason' => 'Rate limit exceeded: too many requests in the last day'];
            }
        }

        // Check max connections limit
        $maxConnections = Config::get('connections.rules.max_connections_per_user', 1000);
        if ($maxConnections > 0) {
            $currentConnections = $this->model->forUser($senderId)->accepted()->count();
            if ($currentConnections >= $maxConnections) {
                return ['can_send' => false, 'reason' => 'Maximum connections limit reached'];
            }
        }

        return ['can_send' => true, 'reason' => 'Can send connection request'];
    }

    /**
     * Get connections with search and filters.
     */
    public function getFilteredConnections(int $userId, array $filters = [], int $perPage = 20)
    {
        $query = $this->model->forUser($userId)
                            ->with(['sender', 'receiver'])
                            ->orderBy('created_at', 'desc');

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by date range
        if (!empty($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }

        // Search by user name or email
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search, $userId) {
                $q->whereHas('sender', function($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('receiver', function($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get recent connection activity for a user.
     */
    public function getRecentActivity(int $userId, int $limit = 10)
    {
        return $this->model->forUser($userId)
                          ->with(['sender', 'receiver'])
                          ->orderBy('updated_at', 'desc')
                          ->limit($limit)
                          ->get();
    }
} 