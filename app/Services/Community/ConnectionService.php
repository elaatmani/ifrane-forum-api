<?php

namespace App\Services\Community;

use App\Models\User;
use App\Models\UserConnection;
use Illuminate\Support\Facades\Log;

class ConnectionService
{
    /**
     * Get complete connection data for a user relationship.
     *
     * @param User $targetUser The user being viewed
     * @param User|null $authUser The authenticated user viewing the profile
     * @return array
     */
    public function getConnectionData(User $targetUser, ?User $authUser): array
    {
        if (!$authUser) {
            return $this->getGuestConnectionData();
        }

        $connection = $this->findConnectionBetweenUsers($authUser, $targetUser);
        $mutualConnections = $this->getMutualConnectionsData($targetUser, $authUser);

        return [
            'id' => $connection?->id,
            'status' => $connection?->status,
            'is_sent_by_me' => $connection ? $connection->isSentBy($authUser->id) : false,
            'can_connect' => $this->canConnect($authUser, $targetUser, $connection),
            'mutual_connections' => $mutualConnections,
            'mutual_connections_count' => count($mutualConnections),
        ];
    }

    /**
     * Get mutual connections data between two users.
     *
     * @param User $targetUser
     * @param User $authUser
     * @return array
     */
    public function getMutualConnectionsData(User $targetUser, User $authUser): array
    {
        try {
            return $authUser->getMutualConnectionsWithProfile($targetUser->id)->toArray();
        } catch (\Exception $e) {
            Log::warning('Failed to get mutual connections', [
                'target_user_id' => $targetUser->id,
                'auth_user_id' => $authUser->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Find connection between two users.
     *
     * @param User $authUser
     * @param User $targetUser
     * @return UserConnection|null
     */
    private function findConnectionBetweenUsers(User $authUser, User $targetUser): ?UserConnection
    {
        return $authUser->allConnections()
            ->where(function($query) use ($authUser, $targetUser) {
                $query->where('sender_id', $authUser->id)
                    ->where('receiver_id', $targetUser->id)
                    ->orWhere('sender_id', $targetUser->id)
                    ->where('receiver_id', $authUser->id);
            })
            ->first();
    }

    /**
     * Determine if authenticated user can connect to target user.
     *
     * @param User $authUser
     * @param User $targetUser
     * @param UserConnection|null $existingConnection
     * @return bool
     */
    private function canConnect(User $authUser, User $targetUser, ?UserConnection $existingConnection): bool
    {
        // Can't connect to yourself
        if ($authUser->id === $targetUser->id) {
            return false;
        }

        // Can't connect if connection already exists
        if ($existingConnection && $existingConnection->status) {
            return false;
        }

        return true;
    }

    /**
     * Get default connection data for guest users.
     *
     * @return array
     */
    private function getGuestConnectionData(): array
    {
        return [
            'id' => null,
            'status' => null,
            'is_sent_by_me' => false,
            'can_connect' => false,
            'mutual_connections' => [],
            'mutual_connections_count' => 0,
        ];
    }
} 