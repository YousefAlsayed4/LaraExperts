<?php

namespace LaraExperts\Bolt\Filament\Helpers;

use LaraExperts\Bolt\Filament\Enums\Http;
use LaraExperts\Bolt\Filament\Helpers\APIResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Log;

class ExtraExceptionHandeling
{
    public static function handleValidationException(ValidationException $exception): Responsable
    {
        Log::error('Exception caught', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return new APIResponse(
            status: 'fail',
            code: Http::BAD_REQUEST,
            message: 'validation.validation_error',
            errors: $exception->validator->errors()->toArray()
        );
    }

    public static function handleAuthenticationException(AuthenticationException $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::BAD_REQUEST,
            message: 'validation.auth_error',
            errors: ['token' =>[ __('validation.token_invalid')]]
        );
    }

    public static function handleErrorException(\ErrorException $exception): Responsable
    {
        Log::error('Exception caught', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return new APIResponse(
            status: 'fail',
            code: Http::BAD_REQUEST,
            message: 'validation.error_exception'
        );
    }

    public static function handleModelNotFoundException(ModelNotFoundException|NotFoundHttpException $exception): Responsable
    {


        $model = explode('\\', $exception->getMessage());

        $model = explode(']', last($model));

        return new APIResponse(
            status: 'fail',
            code: Http::BAD_REQUEST,
            message: __('validation.'.($model[0])) .' '.__('validation.not_found'),

        );
    }

    public static function handleAccessDeniedHttpException(\Exception $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::BAD_REQUEST,
            message: 'validation.not_authorized'
        );
    }

    public static function handlePostTooLargeException(PostTooLargeException $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::BAD_REQUEST,
            message: 'validation.payload_large'
        );
    }

    public static function handleThrottleRequestsException(ThrottleRequestsException $exception): Responsable
    {
        return new APIResponse(
            status: 'fail',
            code: Http::BAD_REQUEST,
            message: 'validation.throttle'
        );
    }
}
