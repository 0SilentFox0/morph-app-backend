<?php

namespace App\Events;

use App\Models\ClientInvitation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientInvitationSent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ClientInvitation $invitation,
    ) {}
}
