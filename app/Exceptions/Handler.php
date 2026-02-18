<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    

    /**
     * Einheitliche JSON-Fehlerbehandlung für API-Requests.
     */
    public function render($request, Throwable $e)
    {
        $expectsJson = $request->expectsJson() || $request->is('api/*');

        if (! $expectsJson) {
            return parent::render($request, $e);
        }

        //  422 Validation
        if ($e instanceof ValidationException) {
            return $this->jsonError(
                status: 422,
                type: 'validation_error',
                message: 'Die übermittelten Daten sind ungültig.',
                details: ['errors' => $e->errors()]
            );
        }

        //  401 Authentifizierung
        if ($e instanceof AuthenticationException) {
            return $this->jsonError(401, 'unauthorized', 'Authentifizierung erforderlich.');
        }

        //  403/404 Policies
        if ($e instanceof AuthorizationException) {
            $status = method_exists($e, 'status') ? $e->status() : 403;
            $type   = $status === 404 ? 'not_found' : 'forbidden';
            $msg    = $status === 404 ? 'Ressource wurde nicht gefunden.' : 'Zugriff verweigert.';
            return $this->jsonError($status, $type, $msg);
        }

        //  404 Model nicht gefunden
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return $this->jsonError(404, 'not_found', 'Ressource wurde nicht gefunden.');
        }

        //  405 Falsche HTTP-Methode
        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->jsonError(405, 'method_not_allowed', 'HTTP-Methode für diese Route nicht erlaubt.');
        }

        //  429 Rate Limiting
        if ($e instanceof ThrottleRequestsException) {
            return $this->jsonError(429, 'too_many_requests', 'Zu viele Anfragen. Bitte später erneut versuchen.');
        }

        //  4xx generische HttpExceptions
        if ($e instanceof HttpException) {
            return $this->jsonError(
                status: $e->getStatusCode(),
                type: 'http_error',
                message: $e->getMessage() ?: 'Anfrage konnte nicht verarbeitet werden.'
            );
        }

        //  Datenbankfehler (QueryException)
        if ($e instanceof QueryException) {
            if ($e->getCode() === '23000') {
                return $this->jsonError(409, 'conflict', 'Datenkonflikt: Eindeutigkeit oder Fremdschlüsselverletzung.');
            }
            return $this->jsonError(500, 'database_error', 'Datenbankfehler. Bitte später erneut versuchen.');
        }

        //  Unerwartete Fehler (500)
        return $this->jsonError(
            status: 500,
            type: 'server_error',
            message: 'Unerwarteter Serverfehler.',
            details: app()->environment('local') ? [
                'exception' => class_basename($e),
                'message'   => $e->getMessage(),
                'trace'     => collect($e->getTrace())->take(3),
            ] : null
        );
    }

    /**
     * Helper für konsistente Fehlerstruktur.
     */
    private function jsonError(int $status, string $type, string $message, array $details = null)
    {
        $payload = [
            'error' => [
                'type'    => $type,
                'message' => $message,
            ],
        ];

        if ($details) {
            $payload['error']['details'] = $details;
        }

        return response()->json($payload, $status);
    }
}
