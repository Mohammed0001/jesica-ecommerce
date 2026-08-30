<?php

use App\Support\ErrorReference;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Every logged exception carries the reference the visitor was shown,
        // plus the request details needed to reproduce it. The HTML error
        // pages read the same reference back via ErrorReference::current().
        $exceptions->context(fn () => [
            'reference' => ErrorReference::current(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_id' => Auth::id(),
        ]);

        // Callers expecting JSON (the add-to-cart button, the promo form, the
        // search box) get the actual reason instead of a bare status code.
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!$request->expectsJson() && !$request->ajax()) {
                return null;
            }

            // Laravel already renders these with correct statuses and useful,
            // structured bodies (422 with field errors, 401, 403, ...).
            $handledByFramework = [
                ValidationException::class,
                AuthenticationException::class,
                AuthorizationException::class,
                HttpResponseException::class,
            ];

            foreach ($handledByFramework as $class) {
                if ($e instanceof $class) {
                    return null;
                }
            }

            $reference = ErrorReference::current();

            if ($e instanceof TokenMismatchException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your session expired before this was submitted. Reload the page and try again — nothing was saved.',
                    'reference' => $reference,
                ], 419);
            }

            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'That item no longer exists. Reload the page to see what is currently available.',
                    'reference' => $reference,
                ], 404);
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            return response()->json([
                'success' => false,
                'message' => $status < 500
                    ? ($e->getMessage() ?: 'That request could not be completed.')
                    : 'Something broke on our side while handling this request. Nothing was charged.',
                // The raw message is only safe to expose with debug on.
                'detail' => config('app.debug') ? $e->getMessage() : null,
                'reference' => $reference,
            ], $status);
        });
    })->create();
