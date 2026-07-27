<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\AuthenticateJwt;
use App\Http\Middleware\AuthenticateMcp;
use App\Http\Middleware\CorrelationId;
use App\Http\Middleware\RequestMetrics;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt' => AuthenticateJwt::class,
            'mcp.auth' => AuthenticateMcp::class,
        ]);
        $middleware->append(CorrelationId::class);
        $middleware->append(RequestMetrics::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn (Request $request, Throwable $e): bool => $request->is('api/*') || $request->is('mcp')
        );

        $exceptions->render(function (ApiException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => $e->errorCode,
                    'message' => $e->getMessage(),
                    'correlationId' => $request->attributes->get('correlation_id'),
                ],
            ], $e->status);
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The request data is invalid.',
                    'details' => $e->errors(),
                    'correlationId' => $request->attributes->get('correlation_id'),
                ],
            ], 422);
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            return response()->json([
                'error' => [
                    'code' => match ($e->getStatusCode()) {
                        404 => 'NOT_FOUND',
                        405 => 'METHOD_NOT_ALLOWED',
                        default => 'HTTP_ERROR',
                    },
                    'message' => $e->getMessage() ?: 'HTTP request failed.',
                    'correlationId' => $request->attributes->get('correlation_id'),
                ],
            ], $e->getStatusCode());
        });
    })->create();
