<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Reminders for anything due or overdue. Daily rather than hourly: the
        // things being watched move on a scale of weeks, and the dispatcher
        // dedupes per interval so a re-run cannot re-send.
        $schedule->command('reminders:send')->dailyAt('08:00')->withoutOverlapping();

        // Recalls are rare and NHTSA is a free public API, so weekly is plenty.
        $schedule->command('recalls:check')->weeklyOn(1, '03:00')->withoutOverlapping();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['sidebar_state']);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // A body over post_max_size leaves $_POST and $_FILES both empty, so no
        // validation rule ever runs. ValidatePostSize turns that into a 413,
        // which Inertia would otherwise surface as a raw error modal.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if (! $request->hasHeader('X-Inertia')) {
                return null;
            }

            return back()->withErrors([
                'photos' => __('That was too much to upload at once. Try adding fewer photos.'),
            ]);
        });
    })->create();
