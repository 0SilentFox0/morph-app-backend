<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'FitConnect API',
    description: 'Backend API for FitConnect — a fitness platform connecting trainers and clients. Provides authentication, client management, exercise/program libraries, session scheduling, real-time workout tracking, chat, notifications, packages, transactions, and progress metrics.',
    contact: new OA\Contact(email: 'support@fitconnect.app'),
)]
#[OA\Server(url: '/api/v1', description: 'API v1')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Enter your Sanctum access token',
)]
#[OA\Tag(name: 'Auth', description: 'Authentication & account management')]
#[OA\Tag(name: 'Users', description: 'User profile & settings')]
#[OA\Tag(name: 'Clients', description: 'Client CRM management')]
#[OA\Tag(name: 'Exercises', description: 'Exercise library')]
#[OA\Tag(name: 'Programs', description: 'Training programs')]
#[OA\Tag(name: 'Sessions', description: 'Training session scheduling')]
#[OA\Tag(name: 'Chat', description: 'Conversations & messaging')]
#[OA\Tag(name: 'Workouts', description: 'Real-time workout tracking')]
#[OA\Tag(name: 'Packages', description: 'Package templates & client packages')]
#[OA\Tag(name: 'Transactions', description: 'Payments & withdrawals')]
#[OA\Tag(name: 'Progress', description: 'Body measurements & personal records')]
#[OA\Tag(name: 'Notifications', description: 'In-app & push notifications')]
class OpenApiConfig
{
}
