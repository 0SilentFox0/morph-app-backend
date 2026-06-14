<?php

namespace App\Jobs;

use App\Models\ClientPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PackageExpirationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find packages expiring within the next 3 days that haven't been reminded yet
        $soonExpiring = ClientPackage::active()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(3))
            ->where('expires_at', '>', now())
            ->whereNull('expiry_reminded_at')
            ->get();

        foreach ($soonExpiring as $package) {
            // TODO: Send expiry reminder notification
            $package->update(['expiry_reminded_at' => now()]);
        }

        // Expire packages that have passed their expiration date
        $expired = ClientPackage::active()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $package) {
            $package->update(['status' => 'expired']);
            // TODO: Send expired notification
        }
    }
}
