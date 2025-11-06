<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\CommonMark\CommonMarkConverter;

class PollinationsService implements AiServiceInterface
{
    protected string $baseUrl = 'https://text.pollinations.ai';
    protected string $imageUrl = 'https://image.pollinations.ai/prompt';

    public function chat(array $messages, array $options = []): array
    {
        try {
            // Get the last user message
            $lastMessage = collect($messages)->last(fn($msg) => $msg['role'] === 'user');
            $prompt = $lastMessage['content'] ?? '';

            $response = Http::timeout(120)
                ->connectTimeout(30)
                ->post($this->baseUrl, [
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                $errorBody = $response->body();
                $errorMessage = 'Pollinations API request failed';

                // Try to parse JSON error
                try {
                    $errorData = json_decode($errorBody, true);
                    $errorMessage = $errorData['error']['message'] ?? $errorData['message'] ?? $errorBody;
                } catch (\Exception $e) {
                    $errorMessage = $errorBody ?: 'Pollinations API request failed';
                }

                Log::error('Pollinations API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error' => $errorMessage,
                ]);

                throw new \Exception($errorMessage);
            }

            $content = $response->body();
            $tokens = $this->countTokens($content);

          $converter = new CommonMarkConverter;
          $html = $converter->convert($content);
          $reply = (string) $html;
            return [
                'content' => $reply,
                'tokens' => $tokens,
                'model' => 'pollinations-text',
            ];
        } catch (\Exception $e) {
            Log::error('Pollinations Service Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function streamChat(array $messages, array $options = []): \Generator
    {
        try {
            $response = Http::timeout(120)->post($this->baseUrl, [
                'messages' => $messages,
            ]);

            $stream = $response->toPsrResponse()->getBody();

            while (!$stream->eof()) {
                $chunk = $stream->read(1024);
                if (!empty($chunk)) {
                    yield $chunk;
                }
            }
        } catch (\Exception $e) {
            Log::error('Pollinations Stream Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): string
    {
        try {
            $width = $options['width'] ?? 1024;
            $height = $options['height'] ?? 1024;
            $model = $options['model'] ?? 'flux';
            $seed = $options['seed'] ?? rand(1, 1000000);

            // Pollinations uses a simple URL structure for image generation
            $imageUrl = $this->imageUrl . '/' . urlencode($prompt);
            $imageUrl .= "?width={$width}&height={$height}&model={$model}&seed={$seed}&nologo=true";

            // Download the image
            $response = Http::timeout(120)
                ->connectTimeout(30)
                ->get($imageUrl);

            if ($response->failed()) {
                throw new \Exception('Failed to generate image. Please try again.');
            }

            // Save to temporary file
            $tempPath = storage_path('app/temp/pollinations_' . uniqid() . '.png');
            if (!is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            file_put_contents($tempPath, $response->body());

            return $tempPath;
        } catch (\Exception $e) {
            Log::error('Pollinations Image Generation Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getAvailableModels(): array
    {
        return [
            'pollinations-text' => 'Pollinations Text',
            'flux' => 'Flux (Image)',
            'flux-realism' => 'Flux Realism (Image)',
            'flux-anime' => 'Flux Anime (Image)',
            'flux-3d' => 'Flux 3D (Image)',
        ];
    }

    public function countTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }
}
