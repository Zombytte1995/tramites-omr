<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Lanzada cuando Gemini API no está disponible o no está configurada.
 * Mapea a HTTP 503 en el Handler. Se excluye de los logs de error
 * porque es ruido operacional, no un bug de la aplicación.
 */
class GeminiException extends RuntimeException {}
