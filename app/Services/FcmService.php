<?php

namespace App\Services;

use App\Models\DeviceToken;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmService
{
    protected Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * Enviar notificación a un usuario específico.
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): array
    {
        $tokens = DeviceToken::where('user_id', $userId)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'message' => 'No tokens found'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Enviar notificación a múltiples usuarios.
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): array
    {
        $tokens = DeviceToken::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'message' => 'No tokens found'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Enviar notificación a todos los usuarios activos.
     */
    public function sendToAll(string $title, string $body, array $data = []): array
    {
        $tokens = DeviceToken::where('is_active', true)
            ->pluck('token')
            ->toArray();

        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0, 'message' => 'No tokens found'];
        }

        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Enviar a una lista de tokens FCM.
     */
    protected function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $success = 0;
        $failure = 0;
        $invalidTokens = [];

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::fromArray([
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                ]);

                $this->messaging->send($message);
                $success++;
            } catch (\Kreait\Firebase\Exception\Messaging\NotFound |
                     \Kreait\Firebase\Exception\Messaging\InvalidMessage $e) {
                $invalidTokens[] = $token;
                $failure++;
            } catch (\Throwable $e) {
                $failure++;
            }
        }

        if (!empty($invalidTokens)) {
            DeviceToken::whereIn('token', $invalidTokens)->update(['is_active' => false]);
        }

        return [
            'success' => $success,
            'failure' => $failure,
            'invalid_tokens' => count($invalidTokens),
        ];
    }
}
