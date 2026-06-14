<?php

namespace App\Services;

use App\Models\User;
use App\Events\PackageAssigned;
use App\Events\PackageExhausted;
use App\Models\ClientPackage;
use App\Models\PackageTemplate;
use Illuminate\Contracts\Pagination\CursorPaginator;

class PackageService
{
    public function listTemplates(User $trainer): CursorPaginator
    {
        return PackageTemplate::forTrainer($trainer->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->cursorPaginate(perPage: 15);
    }

    public function createTemplate(User $trainer, array $data): PackageTemplate
    {
        $data['trainer_id'] = $trainer->id;

        return PackageTemplate::create($data);
    }

    public function updateTemplate(PackageTemplate $template, array $data): PackageTemplate
    {
        $template->update($data);

        return $template->refresh();
    }

    public function archiveTemplate(PackageTemplate $template): void
    {
        $template->update(['archived_at' => now()]);
    }

    public function listClientPackages(User $trainer, array $filters = []): CursorPaginator
    {
        $query = ClientPackage::forTrainer($trainer->id);

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['has_debt']) && $filters['has_debt']) {
            $query->withDebt();
        }

        $query->orderBy('created_at', 'desc');

        return $query->cursorPaginate(perPage: $filters['per_page'] ?? 15);
    }

    public function assignPackage(User $trainer, array $data): ClientPackage
    {
        $data['trainer_id'] = $trainer->id;
        $data['assigned_at'] = now();
        $data['status'] = 'active';

        if (isset($data['template_id'])) {
            $template = PackageTemplate::findOrFail($data['template_id']);
            $data['kind'] = $data['kind'] ?? $template->kind;
            $data['sessions_count'] = $data['sessions_count'] ?? $template->sessions_count;
            $data['remaining_sessions'] = $data['sessions_count'];
            $data['validity_days'] = $data['validity_days'] ?? $template->validity_days;
            $data['price'] = $data['price'] ?? $template->price;
            $data['currency'] = $data['currency'] ?? $template->currency;
            $data['auto_renew'] = $data['auto_renew'] ?? $template->auto_renew_default;
        } else {
            $data['remaining_sessions'] = $data['sessions_count'] ?? null;
        }

        if (isset($data['validity_days'])) {
            $data['expires_at'] = now()->addDays($data['validity_days']);
        }

        $package = ClientPackage::create($data);

        PackageAssigned::dispatch($package);

        return $package;
    }

    public function showPackage(ClientPackage $package): ClientPackage
    {
        return $package;
    }

    public function archivePackage(ClientPackage $package): void
    {
        $package->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    public function decrementSession(ClientPackage $package): ClientPackage
    {
        $package->decrementSession();

        if ($package->isExhausted()) {
            $package->update(['status' => 'exhausted']);
            PackageExhausted::dispatch($package);
        }

        return $package;
    }
}
