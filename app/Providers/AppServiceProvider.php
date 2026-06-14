<?php

namespace App\Providers;

use App\Models\Client;
use App\Policies\ClientPolicy;
use App\Models\ClientPackage;
use App\Models\PackageTemplate;
use App\Policies\ClientPackagePolicy;
use App\Policies\PackageTemplatePolicy;
use App\Models\BodyMeasurement;
use App\Policies\ProgressPolicy;
use App\Models\Exercise;
use App\Models\Program;
use App\Policies\ExercisePolicy;
use App\Policies\ProgramPolicy;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Policies\TransactionPolicy;
use App\Policies\WithdrawalPolicy;
use App\Models\TrainingSession;
use App\Policies\SessionPolicy;
use App\Models\Conversation;
use App\Models\Message;
use App\Policies\ConversationPolicy;
use App\Policies\MessagePolicy;
use App\Models\WorkoutLog;
use App\Policies\WorkoutLogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(Exercise::class, ExercisePolicy::class);
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(PackageTemplate::class, PackageTemplatePolicy::class);
        Gate::policy(ClientPackage::class, ClientPackagePolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Withdrawal::class, WithdrawalPolicy::class);
        Gate::policy(BodyMeasurement::class, ProgressPolicy::class);
        Gate::policy(TrainingSession::class, SessionPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(WorkoutLog::class, WorkoutLogPolicy::class);
    }
}
