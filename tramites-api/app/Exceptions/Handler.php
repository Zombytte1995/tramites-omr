<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(function (NotFoundHttpException $_e, Request $request) {
            if ($this->isApiRequest($request)) {
                return $this->error('Registro no encontrado.', 404);
            }
        });

        $this->renderable(function (DomainException $e, Request $request) {
            if ($this->isApiRequest($request)) {
                return $this->error($e->getMessage(), 422);
            }
        });

        $this->renderable(function (ValidationException $e, Request $request) {
            if ($this->isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los datos proporcionados no son válidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (AuthenticationException $_e, Request $request) {
            if ($this->isApiRequest($request)) {
                return $this->error('No autenticado.', 401);
            }
        });
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => (object) [],
        ], $status);
    }
}
