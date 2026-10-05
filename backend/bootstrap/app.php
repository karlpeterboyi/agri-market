<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'subscription.active'
            => \App\Http\Middleware\HasActiveSubscription::class,

        'subscription.listing'
            => \App\Http\Middleware\RequireListingSubscription::class,

        'subscription.feature'
            => \App\Http\Middleware\CheckSubscriptionFeature::class,

        'role'
            => \Spatie\Permission\Middleware\RoleMiddleware::class,

        'permission'
            => \Spatie\Permission\Middleware\PermissionMiddleware::class,

        'role_or_permission'
            => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

        'user.role'
            => \App\Http\Middleware\EnsureUserRole::class,
    ]);
})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
