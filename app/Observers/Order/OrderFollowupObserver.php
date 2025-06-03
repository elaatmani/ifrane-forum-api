<?php

namespace App\Observers\Order;

use App\Enums\OrderConfirmationEnum;
use App\Models\Order;
use App\Repositories\Contracts\OptionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

class OrderFollowupObserver
{

    protected $optionRepository;
    protected $userRepository;

    public function __construct(OptionRepositoryInterface $optionRepository, UserRepositoryInterface $userRepository)
    {
        $this->optionRepository = $optionRepository;
        $this->userRepository = $userRepository;
    }
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updaing" event.
     */
    public function updating(Order $order): void
    {
        
        $oldAttributes = $order->getOriginal(); // Old values
        $newAttributes = $order->getAttributes(); // New values

        if ((data_get($newAttributes, 'delivery_id', null) != null)
            && data_get($newAttributes, 'agent_status', null) ==  OrderConfirmationEnum::CONFIRMED->value
            && data_get($newAttributes, 'followup_id', null) == null
        ) {
            // Step 1: Retrieve all follow-up users
            $followups = $this->userRepository->findByRole('followup', false)->where('is_active', true)->get();

            // Step 2: Get the last follow-up user ID from the options
            $lastFollowup = $this->optionRepository->getOptionByName('last_followup')?->value;

            // Step 3: Find the index of the last follow-up in the list
            $lastIndex = $followups->search(function ($user) use ($lastFollowup) {
                return $user->id == $lastFollowup;
            });

            // Step 4: Determine the next follow-up user
            if ($lastIndex === false || $lastIndex === $followups->count() - 1) {
                // Start over from the beginning if not found or at the end of the list
                $nextFollowup = $followups->first();
            } else {
                // Get the next follow-up user in the list
                $nextFollowup = $followups[$lastIndex + 1];
            }

            
            
            // Step 5: Save the new follow-up user ID in the database
            $order->followup_id = $nextFollowup->id;
            $order->followup_assigned_at = now();
            $this->optionRepository->setOption('last_followup', $nextFollowup->id);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
