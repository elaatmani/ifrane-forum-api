<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use App\Models\UserConnection;

interface UserConnectionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Send a connection request from one user to another.
     *
     * @param int $senderId
     * @param int $receiverId
     * @param string $message
     * @return UserConnection|null
     */
    public function sendConnectionRequest(int $senderId, int $receiverId, string $message): ?UserConnection;

    /**
     * Accept a connection request.
     *
     * @param int $connectionId
     * @param int $userId
     * @return UserConnection|null
     */
    public function acceptConnectionRequest(int $connectionId, int $userId): ?UserConnection;

    /**
     * Decline a connection request.
     *
     * @param int $connectionId
     * @param int $userId
     * @return UserConnection|null
     */
    public function declineConnectionRequest(int $connectionId, int $userId): ?UserConnection;

    /**
     * Cancel a connection request.
     *
     * @param int $connectionId
     * @param int $userId
     * @return UserConnection|null
     */
    public function cancelConnectionRequest(int $connectionId, int $userId): ?UserConnection;

    /**
     * Remove/Block a connection.
     *
     * @param int $connectionId
     * @param int $userId
     * @return bool
     */
    public function removeConnection(int $connectionId, int $userId): bool;

    /**
     * Get all connections for a user with optional status filter.
     *
     * @param int $userId
     * @param string|null $status
     * @param int $perPage
     * @return mixed
     */
    public function getUserConnections(int $userId, ?string $status = null, int $perPage = 20);

    /**
     * Get connection requests received by a user.
     *
     * @param int $userId
     * @param string|null $status
     * @param int $perPage
     * @return mixed
     */
    public function getReceivedRequests(int $userId, ?string $status = null, int $perPage = 20);

    /**
     * Get connection requests sent by a user.
     *
     * @param int $userId
     * @param string|null $status
     * @param int $perPage
     * @return mixed
     */
    public function getSentRequests(int $userId, ?string $status = null, int $perPage = 20);

    /**
     * Check if a connection exists between two users.
     *
     * @param int $userId1
     * @param int $userId2
     * @param string|null $status
     * @return bool
     */
    public function connectionExists(int $userId1, int $userId2, ?string $status = null): bool;

    /**
     * Get connection between two users.
     *
     * @param int $userId1
     * @param int $userId2
     * @return UserConnection|null
     */
    public function getConnectionBetweenUsers(int $userId1, int $userId2): ?UserConnection;

    /**
     * Get mutual connections between two users.
     *
     * @param int $userId1
     * @param int $userId2
     * @return mixed
     */
    public function getMutualConnections(int $userId1, int $userId2);

    /**
     * Get connection statistics for a user.
     *
     * @param int $userId
     * @return array
     */
    public function getConnectionStats(int $userId): array;

    /**
     * Check if user can send connection request to another user.
     *
     * @param int $senderId
     * @param int $receiverId
     * @return array ['can_send' => bool, 'reason' => string]
     */
    public function canSendConnectionRequest(int $senderId, int $receiverId): array;

    /**
     * Get connections with search and filters.
     *
     * @param int $userId
     * @param array $filters
     * @param int $perPage
     * @return mixed
     */
    public function getFilteredConnections(int $userId, array $filters = [], int $perPage = 20);

    /**
     * Get recent connection activity for a user.
     *
     * @param int $userId
     * @param int $limit
     * @return mixed
     */
    public function getRecentActivity(int $userId, int $limit = 10);
} 