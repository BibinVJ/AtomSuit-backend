<?php

use App\Exceptions\OtpVerificationException;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {
        // Alias custom middleware
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'log.webhook'        => \App\Http\Middleware\LogWebhookRequests::class,
        ]);

        // Force all API responses to be JSON
        $middleware->append(\App\Http\Middleware\ForceJsonResponse::class);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        // Authentication errors (Passport & Laravel)
        $exceptions->render(fn(\Laravel\Passport\Exceptions\AuthenticationException $e) => ApiResponse::error('Unauthenticated.', [], Response::HTTP_UNAUTHORIZED));
        $exceptions->render(fn(\Illuminate\Auth\AuthenticationException $e) => ApiResponse::error('Unauthenticated.', [], Response::HTTP_UNAUTHORIZED));

        // Validation errors
        $exceptions->render(
            fn(\Illuminate\Validation\ValidationException $e, $req) =>
            ApiResponse::error('Validation error.', $e ? $e->errors() : [], Response::HTTP_UNPROCESSABLE_ENTITY)
        );

        // Authorization errors (Spatie)
        $exceptions->render(
            fn(\Spatie\Permission\Exceptions\UnauthorizedException $e, $req) =>
            ApiResponse::error('You do not have the required authorization.', [], Response::HTTP_FORBIDDEN)
        );

        $exceptions->render(
            fn(OtpVerificationException $e, $req) =>
            ApiResponse::error($e->getMessage(), [], Response::HTTP_BAD_REQUEST)
        );

        // resource not found errors
        $exceptions->render(function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($e->getPrevious() instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return ApiResponse::error('Resource not found.', [], Response::HTTP_NOT_FOUND);
            }
        });

        // Catch-all for unhandled exceptions
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                $status = $e instanceof HttpException ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

                return ApiResponse::error(
                    $e->getMessage(),
                    config('app.debug') ? ['trace' => $e->getTrace()] : [],
                    $status
                );
            }

            return null;
        });
    })

    ->create();
