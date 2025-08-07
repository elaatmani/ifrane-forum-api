<?php

namespace App\Repositories\Eloquent;

use App\Models\Conversation;
use App\Models\User;
use App\Models\Session;
use App\Models\Company;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class ConversationRepository extends BaseRepository implements ConversationRepositoryInterface
{
    public function __construct(Conversation $model)
    {
        parent::__construct($model);
    }

    /**
     * Get or create direct conversation between two users
     */
    public function getOrCreateDirectConversation(User $user1, User $user2): Conversation
    {
        DB::beginTransaction();

        try {
            // Check if conversation already exists
            $conversation = $this->model->direct()
                ->whereHas('users', function($q) use ($user1) {
                    $q->where('user_id', $user1->id);
                })
                ->whereHas('users', function($q) use ($user2) {
                    $q->where('user_id', $user2->id);
                })
                ->first();

            if (!$conversation) {
                // Create new direct conversation
                $conversation = $this->model->create([
                    'type' => 'direct',
                    'created_by' => $user1->id
                ]);

                // Add both users
                $conversation->users()->attach([$user1->id, $user2->id]);
            }

            DB::commit();
            return $conversation;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get or create session conversation
     */
    public function getOrCreateSessionConversation(Session $session): Conversation
    {
        DB::beginTransaction();

        try {
            $conversation = $this->model->session()
                ->where('session_id', $session->id)
                ->first();

            if (!$conversation) {
                $conversation = $this->model->create([
                    'type' => 'session',
                    'name' => $session->name . ' Chat',
                    'session_id' => $session->id,
                    'created_by' => $session->created_by ?? auth()->id(),
                    'is_active' => true
                ]);

                // Add session participants
                $sessionUsers = $session->users()->pluck('user_id')->toArray();
                if (!empty($sessionUsers)) {
                    $conversation->users()->attach($sessionUsers);
                }
            }

            DB::commit();
            return $conversation;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get or create company conversation between user and company
     */
    public function getOrCreateCompanyConversation(User $user, Company $company): Conversation
    {
        DB::beginTransaction();

        try {
            $conversation = $this->model->company()
                ->where('company_id', $company->id)
                ->whereHas('users', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->first();

            if (!$conversation) {
                $conversation = $this->model->create([
                    'type' => 'company',
                    'name' => $company->name . ' Support',
                    'company_id' => $company->id,
                    'created_by' => $user->id
                ]);

                // Add user and company members
                $companyMembers = $company->users()->pluck('id')->toArray();
                $participants = array_merge([$user->id], $companyMembers);
                $conversation->users()->attach($participants);
            }

            DB::commit();
            return $conversation;

        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get user's direct conversations
     */
    public function getUserDirectConversations(User $user, int $perPage = 20)
    {
        return $this->model->direct()
            ->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('messages') // Only conversations with at least one message
            ->with(['users', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get user's session conversations
     */
    public function getUserSessionConversations(User $user, int $perPage = 20)
    {
        return $this->model->session()
            ->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('messages') // Only conversations with at least one message
            ->with(['session', 'users', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get user's company conversations
     */
    public function getUserCompanyConversations(User $user, int $perPage = 20)
    {
        return $this->model->company()
            ->whereHas('users', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->whereHas('messages') // Only conversations with at least one message
            ->with(['company', 'users', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Add user to conversation
     */
    public function addUserToConversation(Conversation $conversation, User $user): bool
    {
        try {
            $conversation->users()->attach($user->id);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove user from conversation
     */
    public function removeUserFromConversation(Conversation $conversation, User $user): bool
    {
        try {
            $conversation->users()->detach($user->id);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Mark conversation as read for user
     */
    public function markConversationAsRead(Conversation $conversation, User $user): bool
    {
        try {
            $conversation->markAsRead($user);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get conversation participants
     */
    public function getConversationParticipants(Conversation $conversation)
    {
        return $conversation->users()->with('profile')->get();
    }

    /**
     * Check if user is participant in conversation
     */
    public function isUserInConversation(Conversation $conversation, User $user): bool
    {
        return $conversation->users()->where('user_id', $user->id)->exists();
    }
} 