<?php

namespace App\Events;

use App\Models\ClientProgram;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgramAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ClientProgram $clientProgram,
    ) {}
}
