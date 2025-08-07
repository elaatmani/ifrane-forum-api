<?php

namespace App\Repositories\Contracts;

use App\Models\Conversation;
use App\Models\User;
use App\Models\Session;
use App\Models\Company;

interface ConversationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get or create direct conversation between two users
     */
    public function getOrCreateDirectConversation(User $user1, User $user2): Conversation;

    /**
     * Get or create session conversation
     */
    public function getOrCreateSessionConversation(Session $session): Conversation;

    /**
     * Get or create company conversation between user and company
     */
    public function getOrCreateCompanyConversation(User $user, Company $company): Conversation;

    /**
     * Get user's direct conversations
     */
    public function getUserDirectConversations(User $user, int $perPage = 20);

    /**
     * Get user's session conversations
     */
    public function getUserSessionConversations(User $user, int $perPage = 20);

    /**
     * Get user's company conversations
     */
    public function getUserCompanyConversations(User $user, int $perPage = 20);

    /**
     * Add user to conversation
     */
    public function addUserToConversation(Conversation $conversation, User $user): bool;

    /**
     * Remove user from conversation
     */
    public function removeUserFromConversation(Conversation $conversation, User $user): bool;

    /**
     * Mark conversation as read for user
     */
    public function markConversationAsRead(Conversation $conversation, User $user): bool;

    /**
     * Get conversation participants
     */
    public function getConversationParticipants(Conversation $conversation);

    /**
     * Check if user is participant in conversation
     */
    public function isUserInConversation(Conversation $conversation, User $user): bool;
} 