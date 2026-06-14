<?php

namespace App\Services;

use App\Models\User;
use App\Events\MessageRead;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ChatService
{
    public function getConversations(User $user): CursorPaginator
    {
        return Conversation::query()
            ->whereHas('participants', function ($q) use ($user): void {
                $q->where('user_id', $user->id);
            })
            ->with(['lastMessage', 'participants'])
            ->orderBy('last_message_at', 'desc')
            ->cursorPaginate(perPage: 15);
    }

    public function getOrCreateConversation(User $user, string $otherUserId): Conversation
    {
        // Find existing conversation between the two users
        $conversation = Conversation::query()
            ->whereHas('participants', function ($q) use ($user): void {
                $q->where('user_id', $user->id);
            })
            ->whereHas('participants', function ($q) use ($otherUserId): void {
                $q->where('user_id', $otherUserId);
            })
            ->first();

        if ($conversation) {
            return $conversation->load('participants');
        }

        // Create new conversation
        $conversation = Conversation::create([
            'last_message_at' => null,
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $user->id,
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $otherUserId,
        ]);

        return $conversation->load('participants');
    }

    public function getMessages(Conversation $conversation, ?string $cursor = null): CursorPaginator
    {
        return $conversation->messages()
            ->orderBy('sent_at', 'desc')
            ->cursorPaginate(perPage: 25);
    }

    public function sendMessage(User $sender, Conversation $conversation, array $data): Message
    {
        // Idempotency check via client_message_id
        if (isset($data['client_message_id'])) {
            $existing = Message::where('client_message_id', $data['client_message_id'])
                ->where('conversation_id', $conversation->id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $message = Message::create([
            'conversation_id'  => $conversation->id,
            'sender_id'        => $sender->id,
            'body'             => $data['body'] ?? null,
            'media_file_ids'   => $data['media_file_ids'] ?? null,
            'client_message_id' => $data['client_message_id'] ?? null,
            'sent_at'          => now(),
        ]);

        $conversation->update([
            'last_message_at' => $message->sent_at,
            'last_message_id' => $message->id,
        ]);

        MessageSent::dispatch($message);

        return $message;
    }

    public function markAsRead(User $user, Conversation $conversation, string $messageId): void
    {
        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $now = now();

        $participant->update([
            'last_read_message_id' => $messageId,
            'last_read_at'         => $now,
        ]);

        MessageRead::dispatch(
            $conversation->id,
            $user->id,
            $messageId,
            $now->toIso8601String(),
        );
    }

    public function deleteMessage(Message $message): void
    {
        $message->delete();
    }
}
