<?php

namespace App\Helpers;

use App\Models\AiConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiHelper
{
    /**
     * Envía un mensaje al proveedor IA configurado para la empresa.
     *
     * @param  int|null  $empresaId
     * @param  array     $messages   [ ['role' => 'user'|'assistant', 'content' => '...'] ]
     * @return string    Respuesta del modelo
     *
     * @throws \RuntimeException  Si no hay config activa o falla la llamada
     */
    public static function chat(?int $empresaId, array $messages): string
    {
        $config = AiConfig::forEmpresa($empresaId);

        if (!$config || !$config->activo) {
            throw new \RuntimeException('El asistente IA no está configurado para esta empresa.');
        }

        $apiKey = $config->getApiKeyDecrypted();
        if (!$apiKey) {
            throw new \RuntimeException('API key no válida.');
        }

        // Agregar system prompt si existe
        $systemPrompt = $config->system_prompt
            ?? 'Eres el asistente de BiometricIP, un sistema de control de asistencia biométrica. Ayuda a los usuarios a interpretar datos de asistencia, empleados y reportes.';

        return match ($config->proveedor) {
            'anthropic' => self::callAnthropic($apiKey, $config->modelo, $systemPrompt, $messages),
            'openai'    => self::callOpenAI($apiKey, $config->modelo, $systemPrompt, $messages),
            'deepseek'  => self::callDeepSeek($apiKey, $config->modelo, $systemPrompt, $messages),
            'glm'       => self::callGLM($apiKey, $config->modelo, $systemPrompt, $messages),
            default     => throw new \RuntimeException("Proveedor '{$config->proveedor}' no soportado."),
        };
    }

    // ── Anthropic (Claude) ────────────────────────────────────────────────────

    private static function callAnthropic(string $apiKey, string $model, string $systemPrompt, array $messages): string
    {
        $response = Http::withHeaders([
            'x-api-key'         => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => $model,
            'max_tokens' => 1024,
            'system'     => $systemPrompt,
            'messages'   => $messages,
        ]);

        if ($response->failed()) {
            Log::error('AiHelper Anthropic error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Error al conectar con Anthropic: ' . ($response->json('error.message') ?? $response->status()));
        }

        return $response->json('content.0.text') ?? '';
    }

    // ── OpenAI (ChatGPT) ──────────────────────────────────────────────────────

    private static function callOpenAI(string $apiKey, string $model, string $systemPrompt, array $messages): string
    {
        $allMessages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $messages
        );

        $response = Http::withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'      => $model,
                'max_tokens' => 1024,
                'messages'   => $allMessages,
            ]);

        if ($response->failed()) {
            Log::error('AiHelper OpenAI error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Error al conectar con OpenAI: ' . ($response->json('error.message') ?? $response->status()));
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    // ── DeepSeek (compatible OpenAI) ─────────────────────────────────────────

    private static function callDeepSeek(string $apiKey, string $model, string $systemPrompt, array $messages): string
    {
        $allMessages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $messages
        );

        $response = Http::withToken($apiKey)
            ->post('https://api.deepseek.com/v1/chat/completions', [
                'model'      => $model,
                'max_tokens' => 1024,
                'messages'   => $allMessages,
            ]);

        if ($response->failed()) {
            Log::error('AiHelper DeepSeek error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Error al conectar con DeepSeek: ' . ($response->json('error.message') ?? $response->status()));
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    // ── GLM / Zhipu ──────────────────────────────────────────────────────────

    private static function callGLM(string $apiKey, string $model, string $systemPrompt, array $messages): string
    {
        $allMessages = array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $messages
        );

        $response = Http::withToken($apiKey)
            ->post('https://open.bigmodel.cn/api/paas/v4/chat/completions', [
                'model'    => $model,
                'messages' => $allMessages,
            ]);

        if ($response->failed()) {
            Log::error('AiHelper GLM error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Error al conectar con GLM: ' . ($response->json('error.message') ?? $response->status()));
        }

        return $response->json('choices.0.message.content') ?? '';
    }

    // ── Modelos disponibles por proveedor ─────────────────────────────────────

    public static function modelosPorProveedor(): array
    {
        return [
            'anthropic' => [
                'claude-opus-4-6'              => 'Claude Opus 4.6',
                'claude-sonnet-4-6'            => 'Claude Sonnet 4.6',
                'claude-haiku-4-5-20251001'    => 'Claude Haiku 4.5',
            ],
            'openai' => [
                'gpt-4o'       => 'GPT-4o',
                'gpt-4o-mini'  => 'GPT-4o Mini',
                'gpt-4-turbo'  => 'GPT-4 Turbo',
                'o1-mini'      => 'o1-mini',
            ],
            'deepseek' => [
                'deepseek-chat'   => 'DeepSeek Chat',
                'deepseek-coder'  => 'DeepSeek Coder',
            ],
            'glm' => [
                'glm-4'      => 'GLM-4',
                'glm-4-flash'=> 'GLM-4 Flash',
                'glm-3-turbo'=> 'GLM-3 Turbo',
            ],
        ];
    }
}
