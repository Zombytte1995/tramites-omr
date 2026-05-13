<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected $dontReport = [
        DomainException::class,   // regla de negocio rota, no bug de servidor
        GeminiException::class,   // error operacional de API externa, no bug
    ];

    /**
     * Intercepta todas las excepciones en rutas /api/* antes de que
     * Laravel las transforme (prepareException), permitiendo capturar
     * ModelNotFoundException, AuthorizationException, etc. en su forma original.
     */
    public function render($request, Throwable $e): Response|JsonResponse
    {
        if ($this->isApiRequest($request)) {
            return $this->renderApiException($e);
        }

        return parent::render($request, $e);
    }

    private function renderApiException(Throwable $e): JsonResponse
    {
        [$status, $message, $errors] = $this->classify($e);

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Mapea cualquier Throwable a [status, message, errors].
     * El orden importa: las clases más específicas van primero.
     */
    private function classify(Throwable $e): array
    {
        return match (true) {
            $e instanceof ValidationException => [422, 'Los datos proporcionados no son válidos.', $e->errors()],

            $e instanceof AuthenticationException => [401, 'No autenticado.', (object) []],

            $e instanceof AuthorizationException,
            $e instanceof AccessDeniedHttpException => [403, 'No autorizado.', (object) []],

            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => [404, 'Recurso no encontrado.', (object) []],

            $e instanceof MethodNotAllowedHttpException => [405, 'Método HTTP no permitido.', (object) []],

            $e instanceof DomainException => [422, $e->getMessage(), (object) []],

            $e instanceof GeminiException => [503, $e->getMessage(), (object) []],

            // HttpException genérico: usa el status code propio (ej. 419, 429, 503)
            $e instanceof HttpException => [$e->getStatusCode(), $this->httpStatusMessage($e->getStatusCode()), (object) []],

            // Fallback: cualquier error no contemplado → 500
            default => $this->serverError($e),
        };
    }

    /**
     * En debug devuelve la clase y el mensaje de la excepción para diagnóstico.
     * En producción, solo el mensaje genérico (sin exponer internos).
     */
    private function serverError(Throwable $e): array
    {
        $message = config('app.debug')
            ? sprintf('[%s] %s', class_basename($e), $e->getMessage())
            : 'Error interno del servidor.';

        return [500, $message, (object) []];
    }

    /**
     * Mensajes HTTP estándar en español para HttpExceptions genéricas.
     */
    private function httpStatusMessage(int $status): string
    {
        return match ($status) {
            400 => 'Solicitud incorrecta.',
            401 => 'No autenticado.',
            403 => 'No autorizado.',
            404 => 'Recurso no encontrado.',
            405 => 'Método HTTP no permitido.',
            408 => 'Tiempo de espera de la solicitud agotado.',
            409 => 'Conflicto con el estado actual del recurso.',
            410 => 'El recurso solicitado ya no está disponible.',
            422 => 'Los datos proporcionados no son válidos.',
            429 => 'Demasiadas solicitudes. Intente más tarde.',
            500 => 'Error interno del servidor.',
            502 => 'Error de puerta de enlace.',
            503 => 'Servicio no disponible temporalmente.',
            default => Response::$statusTexts[$status] ?? 'Error desconocido.',
        };
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
