<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Message;

class MessagePolicy
{
    public function delete(User $user, Message $message): bool
    {
        return $message->sender_id === $user->id;
    }
}
