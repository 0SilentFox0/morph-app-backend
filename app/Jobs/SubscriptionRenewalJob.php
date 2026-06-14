<?php

namespace App\Jobs;

use App\Models\ClientPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SubscriptionRenewalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find packages that are exhausted or expired and have auto_renew enabled
        $renewablePackages = ClientPackage::where('auto_renew', true)
            ->whereIn('status', ['exhausted', 'expired'])
            ->whereNull('auto_renewed_to_id')
            ->get();

        foreach ($renewablePackages as $package) {
            $newPackage = ClientPackage::create([
                'client_id' => $package->client_id,
                'trainer_id' => $package->trainer_id,
                'template_id' => $package->template_id,
                'kind' => $package->kind,
                'sessions_count' => $package->sessions_count,
                'remaining_sessions' => $package->sessions_count,
                'validity_days' => $package->validity_days,
                'expires_at' => $package->validity_days ? now()->addDays($package->validity_days) : null,
                'price' => $package->price,
                'currency' => $package->currency,
                'status' => 'active',
                'assigned_at' => now(),
                'auto_renew' => true,
                'debt_since' => now(),
            ]);

            $package->update(['auto_renewed_to_id' => $newPackage->id]);

            // TODO: Send renewal notification to trainer and client
        }
    }
}
