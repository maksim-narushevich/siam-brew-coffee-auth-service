<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\ResponseStatus;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    public static function handle(Throwable $exception, Request $request): ?Response
    {
        if (! $request->is('api/*')) {
            return null;
        }

        $status = match (get_class($exception)) {
            AuthenticationException::class => ResponseStatus::UNAUTHORIZED,
            ValidationException::class => ResponseStatus::HTTP_BAD_REQUEST,
            NotFoundHttpException::class => ResponseStatus::NOT_FOUND,
            AccessDeniedHttpException::class => ResponseStatus::HTTP_FORBIDDEN,
            default => ResponseStatus::HTTP_INTERNAL_SERVER_ERROR,
        };

        $message = match ($status->name) {
            ResponseStatus::NOT_FOUND->name => 'Not found.',
            default => $exception->getMessage()
        };

        return response()->json(['status' => $status->value, 'message' => $message], $status->value);
    }
}
