<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->method(), ['POST', 'PATCH', 'PUT'])) {
            return $next($request);
        }

        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey) {
            return $next($request);
        }

        $userId = $request->user()?->id;

        if (!$userId) {
            return $next($request);
        }

        $existing = DB::table('idempotency_keys')
            ->where('user_id', $userId)
            ->where('key', $idempotencyKey)
            ->first();

        if ($existing) {
            return response($existing->response_body, $existing->response_code)
                ->header('Content-Type', 'application/json')
                ->header('X-Idempotent-Replay', 'true');
        }

        $response = $next($request);

        if ($response->getStatusCode() < 500) {
            DB::table('idempotency_keys')->insert([
                'id' => Str::uuid()->toString(),
                'key' => $idempotencyKey,
                'user_id' => $userId,
                'response_code' => $response->getStatusCode(),
                'response_body' => $response->getContent(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }
}
