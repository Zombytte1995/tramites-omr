<?php

namespace App\Actions;

use App\Exceptions\GeminiException;
use App\Models\Tramite;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GenerarResumenIaAction
{
    private const CACHE_TTL_HORAS = 24;
    private const TIMEOUT_SEGUNDOS = 20;
    private const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models';

    /**
     * Genera un resumen ejecutivo del trámite usando Gemini API.
     *
     * El resultado se cachea 24 horas para evitar llamadas redundantes
     * sobre el mismo trámite. Cada cambio estructural al trámite que
     * invalide el resumen requiere limpiar la caché manualmente:
     *   Cache::forget("tramite.resumen.{$id}")
     *
     * @throws GeminiException  Si la API key no está configurada o Gemini falla.
     */
    public function execute(Tramite $tramite): string
    {
        $apiKey = config('services.gemini.api_key');

        if (blank($apiKey)) {
            throw new GeminiException(
                'El servicio de resumen IA no está configurado. '
                . 'Defina GEMINI_API_KEY en las variables de entorno.'
            );
        }

        return Cache::remember(
            "tramite.resumen.{$tramite->id}",
            now()->addHours(self::CACHE_TTL_HORAS),
            fn () => $this->llamarGemini($tramite, $apiKey)
        );
    }

    private function llamarGemini(Tramite $tramite, string $apiKey): string
    {
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        $url   = self::API_BASE . "/{$model}:generateContent?key={$apiKey}";

        $prompt = 'Genera un resumen ejecutivo en español, máximo 3 oraciones, '
            . "del siguiente trámite administrativo: {$tramite->nombre} - {$tramite->descripcion}. "
            . 'Enfócate en su propósito y beneficio al ciudadano.';

        try {
            $response = Http::timeout(self::TIMEOUT_SEGUNDOS)
                ->post($url, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                ]);
        } catch (ConnectionException) {
            throw new GeminiException(
                'No se pudo conectar con el servicio de resumen IA. Intente más tarde.'
            );
        }

        if ($response->failed()) {
            $detalle = $response->json('error.message') ?? $response->status();

            throw new GeminiException(
                "El servicio de resumen IA devolvió un error ({$detalle}). Intente más tarde."
            );
        }

        $texto = $response->json('candidates.0.content.parts.0.text');

        if (blank($texto)) {
            throw new GeminiException(
                'El servicio de resumen IA no devolvió una respuesta válida. Intente más tarde.'
            );
        }

        return trim($texto);
    }
}
