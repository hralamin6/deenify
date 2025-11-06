<?php

namespace App\Services\AI;

class AiServiceFactory
{
    public static function make(string $provider): AiServiceInterface
    {
        return match ($provider) {
            'openrouter' => new OpenRouterService(),
            'gemini' => new GeminiService(),
            'pollinations' => new PollinationsService(),
            default => throw new \InvalidArgumentException("Unsupported AI provider: {$provider}"),
        };
    }

    public static function getAvailableProviders(): array
    {
        return [
            'openrouter' => 'OpenRouter',
            'gemini' => 'Google Gemini',
            'pollinations' => 'Pollinations AI',
        ];
    }
}
