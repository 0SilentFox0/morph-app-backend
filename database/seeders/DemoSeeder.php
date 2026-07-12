<?php

namespace Database\Seeders;

use App\Models\BodyMeasurement;
use App\Models\Client;
use App\Models\ClientPackage;
use App\Models\ClientProgram;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Exercise;
use App\Models\Message;
use App\Models\PackageTemplate;
use App\Models\PersonalRecord;
use App\Models\Program;
use App\Models\ProgramExercise;
use App\Models\SessionParticipant;
use App\Models\TrainingSession;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkoutLog;
use App\Models\WorkoutLogExercise;
use App\Models\WorkoutLogSet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $trainer = User::where('email', 'trainer@morph.app')->firstOrFail();
        $clientUser = User::where('email', 'user@morph.app')->firstOrFail();

        // ── 1. Client relationship ──────────────────────────────────
        $client = Client::create([
            'trainer_id' => $trainer->id,
            'user_id' => $clientUser->id,
            'name' => $clientUser->name,
            'email' => $clientUser->email,
            'type' => 'personal',
            'status' => 'active',
            'notes' => 'Мета: схуднення та тонус м\'язів. Тренування 3 рази на тиждень.',
            'tags' => ['weight_loss', 'morning'],
        ]);

        // ── 2. Exercises ────────────────────────────────────────────
        $exercisesData = [
            [
                'name' => 'Присідання зі штангою',
                'description' => 'Базова вправа для ніг. Штанга на трапеціях, присідання до паралелі.',
                'muscle_groups' => ['quadriceps', 'glutes', 'hamstrings'],
                'equipment' => ['barbell', 'squat_rack'],
            ],
            [
                'name' => 'Жим лежачи',
                'description' => 'Класичний жим штанги лежачи на горизонтальній лавці.',
                'muscle_groups' => ['chest', 'triceps', 'front_deltoids'],
                'equipment' => ['barbell', 'bench'],
            ],
            [
                'name' => 'Станова тяга',
                'description' => 'Тяга штанги з підлоги. Класичний стиль.',
                'muscle_groups' => ['back', 'glutes', 'hamstrings'],
                'equipment' => ['barbell'],
            ],
            [
                'name' => 'Підтягування',
                'description' => 'Підтягування широким хватом на перекладині.',
                'muscle_groups' => ['lats', 'biceps', 'rear_deltoids'],
                'equipment' => ['pull_up_bar'],
            ],
            [
                'name' => 'Жим гантелей сидячи',
                'description' => 'Жим гантелей над головою сидячи на лавці з опорою.',
                'muscle_groups' => ['shoulders', 'triceps'],
                'equipment' => ['dumbbells', 'bench'],
            ],
            [
                'name' => 'Випади з гантелями',
                'description' => 'Випади вперед з гантелями в руках.',
                'muscle_groups' => ['quadriceps', 'glutes', 'hamstrings'],
                'equipment' => ['dumbbells'],
            ],
            [
                'name' => 'Тяга штанги в нахилі',
                'description' => 'Тяга штанги до поясу в нахилі для спини.',
                'muscle_groups' => ['lats', 'rhomboids', 'biceps'],
                'equipment' => ['barbell'],
            ],
            [
                'name' => 'Планка',
                'description' => 'Статична вправа на корпус. Утримання позиції.',
                'muscle_groups' => ['core', 'abs', 'lower_back'],
                'equipment' => [],
            ],
        ];

        $exercises = [];
        foreach ($exercisesData as $data) {
            $exercises[] = Exercise::create([
                'trainer_id' => $trainer->id,
                ...$data,
            ]);
        }

        // ── 3. Programs with exercises ──────────────────────────────
        $programA = Program::create([
            'trainer_id' => $trainer->id,
            'name' => 'Силова база — Початковий рівень',
            'description' => 'Програма для початківців. 3 тренування на тиждень, фокус на базових рухах.',
            'difficulty' => 'beginner',
            'estimated_duration_min' => 60,
            'category' => 'strength',
            'price' => 0,
            'price_currency' => 'UAH',
            'views_count' => 24,
            'likes_count' => 3,
        ]);

        $programB = Program::create([
            'trainer_id' => $trainer->id,
            'name' => 'HIIT Жироспалювання',
            'description' => 'Інтенсивне інтервальне тренування для максимального жироспалювання.',
            'difficulty' => 'intermediate',
            'estimated_duration_min' => 45,
            'category' => 'hiit',
            'price' => 500.00,
            'price_currency' => 'UAH',
            'views_count' => 57,
            'likes_count' => 8,
        ]);

        // Program A exercises: squat, bench, deadlift, plank
        $programAExercises = [
            ['exercise' => $exercises[0], 'sets' => 4, 'reps' => 8, 'weight_kg' => 40, 'rest_seconds' => 120],
            ['exercise' => $exercises[1], 'sets' => 4, 'reps' => 8, 'weight_kg' => 30, 'rest_seconds' => 120],
            ['exercise' => $exercises[2], 'sets' => 3, 'reps' => 6, 'weight_kg' => 50, 'rest_seconds' => 150],
            ['exercise' => $exercises[7], 'sets' => 3, 'reps' => 1, 'weight_kg' => null, 'rest_seconds' => 60, 'notes' => 'Утримувати 45 секунд'],
        ];

        foreach ($programAExercises as $i => $pe) {
            ProgramExercise::create([
                'program_id' => $programA->id,
                'exercise_id' => $pe['exercise']->id,
                'order' => $i + 1,
                'sets' => $pe['sets'],
                'reps' => $pe['reps'],
                'weight_kg' => $pe['weight_kg'],
                'rest_seconds' => $pe['rest_seconds'],
                'notes' => $pe['notes'] ?? null,
                'name_snapshot' => $pe['exercise']->name,
            ]);
        }

        // Program B exercises: lunges, pull-ups, shoulder press, rows
        $programBExercises = [
            ['exercise' => $exercises[5], 'sets' => 3, 'reps' => 12, 'weight_kg' => 10, 'rest_seconds' => 60],
            ['exercise' => $exercises[3], 'sets' => 3, 'reps' => 8, 'weight_kg' => null, 'rest_seconds' => 90],
            ['exercise' => $exercises[4], 'sets' => 3, 'reps' => 10, 'weight_kg' => 8, 'rest_seconds' => 60],
            ['exercise' => $exercises[6], 'sets' => 3, 'reps' => 10, 'weight_kg' => 25, 'rest_seconds' => 90],
        ];

        foreach ($programBExercises as $i => $pe) {
            ProgramExercise::create([
                'program_id' => $programB->id,
                'exercise_id' => $pe['exercise']->id,
                'order' => $i + 1,
                'sets' => $pe['sets'],
                'reps' => $pe['reps'],
                'weight_kg' => $pe['weight_kg'],
                'rest_seconds' => $pe['rest_seconds'],
                'name_snapshot' => $pe['exercise']->name,
            ]);
        }

        // ── 4. Assign program to client ─────────────────────────────
        ClientProgram::create([
            'client_id' => $client->id,
            'program_id' => $programA->id,
            'program_snapshot' => [
                'name' => $programA->name,
                'difficulty' => $programA->difficulty,
                'estimated_duration_min' => $programA->estimated_duration_min,
            ],
            'assigned_at' => now()->subWeeks(3),
        ]);

        // ── 5. Package templates ────────────────────────────────────
        $template8 = PackageTemplate::create([
            'trainer_id' => $trainer->id,
            'name' => 'Стандарт — 8 тренувань',
            'kind' => 'count_based',
            'sessions_count' => 8,
            'validity_days' => 30,
            'price' => 3200.00,
            'currency' => 'UAH',
        ]);

        PackageTemplate::create([
            'trainer_id' => $trainer->id,
            'name' => 'Про — 12 тренувань',
            'kind' => 'count_based',
            'sessions_count' => 12,
            'validity_days' => 45,
            'price' => 4200.00,
            'currency' => 'UAH',
            'auto_renew_default' => true,
        ]);

        PackageTemplate::create([
            'trainer_id' => $trainer->id,
            'name' => 'Місячний безліміт',
            'kind' => 'time_based',
            'validity_days' => 30,
            'price' => 5500.00,
            'currency' => 'UAH',
        ]);

        // ── 6. Client package (active) ──────────────────────────────
        $clientPackage = ClientPackage::create([
            'client_id' => $client->id,
            'trainer_id' => $trainer->id,
            'template_id' => $template8->id,
            'kind' => 'count_based',
            'sessions_count' => 8,
            'remaining_sessions' => 5,
            'validity_days' => 30,
            'expires_at' => now()->addDays(16),
            'price' => 3200.00,
            'currency' => 'UAH',
            'status' => 'active',
            'assigned_at' => now()->subWeeks(2),
        ]);

        // ── 7. Training sessions ────────────────────────────────────
        // 3 completed past sessions
        $pastSessions = [];
        for ($i = 3; $i >= 1; $i--) {
            $startAt = now()->subDays($i * 3)->setHour(9)->setMinute(0)->setSecond(0);
            $session = TrainingSession::create([
                'trainer_id' => $trainer->id,
                'title' => "Тренування #{$i} — Силова база",
                'type' => 'personal',
                'start_at' => $startAt,
                'end_at' => $startAt->copy()->addMinutes(60),
                'status' => 'completed',
                'status_changed_at' => $startAt->copy()->addMinutes(60),
                'program_id' => $programA->id,
                'client_package_id' => $clientPackage->id,
            ]);

            SessionParticipant::create([
                'session_id' => $session->id,
                'client_id' => $client->id,
            ]);

            $pastSessions[] = $session;
        }

        // 2 upcoming planned sessions
        foreach ([2, 5] as $daysAhead) {
            $startAt = now()->addDays($daysAhead)->setHour(9)->setMinute(0)->setSecond(0);
            $upcoming = TrainingSession::create([
                'trainer_id' => $trainer->id,
                'title' => 'Силова база — Тренування',
                'type' => 'personal',
                'start_at' => $startAt,
                'end_at' => $startAt->copy()->addMinutes(60),
                'status' => 'planned',
                'program_id' => $programA->id,
                'client_package_id' => $clientPackage->id,
            ]);

            SessionParticipant::create([
                'session_id' => $upcoming->id,
                'client_id' => $client->id,
            ]);
        }

        // 1 canceled session
        $canceledStart = now()->subDays(5)->setHour(9)->setMinute(0)->setSecond(0);
        $canceled = TrainingSession::create([
            'trainer_id' => $trainer->id,
            'title' => 'Силова база — Скасовано',
            'type' => 'personal',
            'start_at' => $canceledStart,
            'end_at' => $canceledStart->copy()->addMinutes(60),
            'status' => 'canceled',
            'status_changed_at' => $canceledStart->copy()->subHours(4),
            'cancellation_reason' => 'client_request',
            'program_id' => $programA->id,
            'client_package_id' => $clientPackage->id,
        ]);

        SessionParticipant::create([
            'session_id' => $canceled->id,
            'client_id' => $client->id,
        ]);

        // ── 8. Workout logs for completed sessions ──────────────────
        $weights = [
            [40, 42.5, 45],    // squat progression
            [30, 30, 32.5],   // bench progression
            [50, 55, 57.5],   // deadlift progression
        ];

        foreach ($pastSessions as $si => $session) {
            $sessionStart = $session->start_at;

            $workoutLog = WorkoutLog::create([
                'session_id' => $session->id,
                'started_at' => $sessionStart,
                'started_by_user_id' => $trainer->id,
                'finished_at' => $sessionStart->copy()->addMinutes(55),
                'finished_by_user_id' => $trainer->id,
                'last_version' => 1,
            ]);

            // Log 3 exercises per workout (squat, bench, deadlift)
            $logExerciseIds = [0, 1, 2];
            foreach ($logExerciseIds as $ei => $exerciseIdx) {
                $exercise = $exercises[$exerciseIdx];
                $weight = $weights[$ei][$si];

                $logExercise = WorkoutLogExercise::create([
                    'workout_log_id' => $workoutLog->id,
                    'exercise_id' => $exercise->id,
                    'order' => $ei + 1,
                    'name_snapshot' => $exercise->name,
                    'planned_sets' => $exerciseIdx === 2 ? 3 : 4,
                    'planned_reps' => $exerciseIdx === 2 ? 6 : 8,
                    'planned_weight_kg' => $weight,
                ]);

                $setsCount = $exerciseIdx === 2 ? 3 : 4;
                $repsTarget = $exerciseIdx === 2 ? 6 : 8;

                for ($setIdx = 0; $setIdx < $setsCount; $setIdx++) {
                    $actualReps = $setIdx < $setsCount - 1 ? $repsTarget : max($repsTarget - 1, 5);
                    $isPr = $si === 2 && $setIdx === 0;

                    WorkoutLogSet::create([
                        'workout_log_id' => $workoutLog->id,
                        'workout_log_exercise_id' => $logExercise->id,
                        'exercise_id' => $exercise->id,
                        'set_index' => $setIdx + 1,
                        'reps' => $actualReps,
                        'weight_kg' => $weight,
                        'rest_seconds' => $exerciseIdx === 2 ? 150 : 120,
                        'performed_at' => $sessionStart->copy()->addMinutes(($ei * 15) + ($setIdx * 3)),
                        'actor_user_id' => $clientUser->id,
                        'is_pr' => $isPr,
                        'client_uuid' => Str::uuid(),
                        'version' => 1,
                    ]);
                }
            }
        }

        // ── 9. Body measurements (over 3 weeks) ────────────────────
        $measurementsData = [
            ['metric_type' => 'weight', 'values' => [72.5, 71.8, 71.2], 'unit' => 'kg'],
            ['metric_type' => 'body_fat_percent', 'values' => [24.0, 23.5, 23.1], 'unit' => '%'],
            ['metric_type' => 'waist', 'values' => [82.0, 81.0, 80.0], 'unit' => 'cm'],
            ['metric_type' => 'chest', 'values' => [92.0, 92.0, 92.5], 'unit' => 'cm'],
            ['metric_type' => 'hips', 'values' => [98.0, 97.0, 96.5], 'unit' => 'cm'],
            ['metric_type' => 'biceps', 'values' => [28.0, 28.5, 29.0], 'unit' => 'cm'],
        ];

        foreach ($measurementsData as $md) {
            foreach ($md['values'] as $wi => $value) {
                BodyMeasurement::create([
                    'client_id' => $client->id,
                    'metric_type' => $md['metric_type'],
                    'value' => $value,
                    'unit' => $md['unit'],
                    'measured_at' => now()->subWeeks(3 - $wi)->startOfWeek(),
                    'recorded_by_user_id' => $trainer->id,
                ]);
            }
        }

        // ── 10. Personal records ────────────────────────────────────
        PersonalRecord::create([
            'client_id' => $client->id,
            'exercise_id' => $exercises[0]->id,
            'weight_kg' => 45,
            'reps' => 8,
            'achieved_at' => now()->subDays(3),
        ]);

        PersonalRecord::create([
            'client_id' => $client->id,
            'exercise_id' => $exercises[1]->id,
            'weight_kg' => 32.5,
            'reps' => 8,
            'achieved_at' => now()->subDays(3),
        ]);

        PersonalRecord::create([
            'client_id' => $client->id,
            'exercise_id' => $exercises[2]->id,
            'weight_kg' => 57.5,
            'reps' => 6,
            'achieved_at' => now()->subDays(3),
        ]);

        // ── 11. Conversation & messages ─────────────────────────────
        $conversation = Conversation::create([
            'last_message_at' => now()->subHours(2),
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $trainer->id,
        ]);

        ConversationParticipant::create([
            'conversation_id' => $conversation->id,
            'user_id' => $clientUser->id,
        ]);

        $chatMessages = [
            ['sender' => $trainer, 'body' => 'Привіт, Маріє! Вітаю у програмі. Завтра о 9:00 перше тренування. Готова?', 'ago_hours' => 72],
            ['sender' => $clientUser, 'body' => 'Привіт! Так, дуже чекаю. Що брати з собою?', 'ago_hours' => 71],
            ['sender' => $trainer, 'body' => 'Зручний спортивний одяг, кросівки, пляшка води та рушник. Все інше є в залі.', 'ago_hours' => 71],
            ['sender' => $clientUser, 'body' => 'Супер, дякую! До зустрічі!', 'ago_hours' => 70],
            ['sender' => $trainer, 'body' => 'Чудове тренування сьогодні! Прогрес вже видно. Присідання пішли набагато краще.', 'ago_hours' => 24],
            ['sender' => $clientUser, 'body' => 'Дякую! Трохи болять м\'язи, але це нормально, так?', 'ago_hours' => 23],
            ['sender' => $trainer, 'body' => 'Так, крепатура перші дні — це нормально. Головне пити воду і добре спати. У четвер наступне тренування.', 'ago_hours' => 22],
            ['sender' => $clientUser, 'body' => 'Зрозуміла, буду готова!', 'ago_hours' => 2],
        ];

        $lastMessage = null;
        foreach ($chatMessages as $msg) {
            $lastMessage = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $msg['sender']->id,
                'body' => $msg['body'],
                'client_message_id' => Str::uuid(),
                'sent_at' => now()->subHours($msg['ago_hours']),
            ]);
        }

        $conversation->update(['last_message_id' => $lastMessage->id]);

        ConversationParticipant::where('conversation_id', $conversation->id)
            ->update([
                'last_read_message_id' => $lastMessage->id,
                'last_read_at' => now(),
            ]);

        // ── 12. Transactions ────────────────────────────────────────
        Transaction::create([
            'trainer_id' => $trainer->id,
            'client_id' => $client->id,
            'client_package_id' => $clientPackage->id,
            'amount' => 3200.00,
            'currency' => 'UAH',
            'method' => 'card',
            'status' => 'paid',
            'paid_at' => now()->subWeeks(2),
            'note' => 'Оплата пакету "Стандарт — 8 тренувань"',
        ]);

        Transaction::create([
            'trainer_id' => $trainer->id,
            'client_id' => $client->id,
            'amount' => 500.00,
            'currency' => 'UAH',
            'method' => 'cash',
            'status' => 'paid',
            'paid_at' => now()->subWeeks(1),
            'note' => 'Разове тренування (поза пакетом)',
        ]);
    }
}
