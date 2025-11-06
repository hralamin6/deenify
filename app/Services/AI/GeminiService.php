<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\CommonMark\CommonMarkConverter;

class GeminiService implements AiServiceInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta';
    protected string $defaultModel = 'gemini-pro';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;

        try {
            // Convert OpenAI format to Gemini format
            $contents = $this->convertMessagesToGeminiFormat($messages);

            $url = $this->baseUrl . "/models/{$model}:generateContent";

            $response = Http::timeout(120)
                ->connectTimeout(30)
                ->withQueryParameters(['key' => $this->apiKey])
                ->post($url, [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => $maxTokens,
                    ],
                ]);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $errorBody['message'] ?? $response->body();

                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error' => $errorMessage,
                ]);

                throw new \Exception($errorMessage, $response->status());
            }

            $data = $response->json();
          $markdown = data_get($data, 'candidates.0.content.parts.0.text', '');
          //            dd($response);
          if ($markdown === '') {
            throw new \RuntimeException('Empty response from AI');
          }

          $converter = new CommonMarkConverter;
          $html = $converter->convert($markdown);
          $reply = (string) $html;
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $tokens = $this->countTokens($content);

            return [
                'content' => $reply,
                'tokens' => $tokens,
                'model' => $model,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini Service Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function streamChat(array $messages, array $options = []): \Generator
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;

        try {
            $contents = $this->convertMessagesToGeminiFormat($messages);

            $url = $this->baseUrl . "/models/{$model}:streamGenerateContent";

            $response = Http::timeout(120)
                ->connectTimeout(30)
                ->withQueryParameters(['key' => $this->apiKey])
                ->post($url, [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => $maxTokens,
                    ],
                ]);

            $stream = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (!$stream->eof()) {
                $chunk = $stream->read(1024);
                $buffer .= $chunk;

                $lines = explode("\n", $buffer);
                $buffer = array_pop($lines);

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) {
                        continue;
                    }

                    $data = json_decode($line, true);
                    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                        yield $data['candidates'][0]['content']['parts'][0]['text'];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Gemini Stream Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): string
    {
        throw new \Exception('Image generation not supported by Gemini. Use Pollinations service.');
    }

    public function getAvailableModels(): array
    {


return [
  // 🌟 GEMINI 2.5 SERIES — Free Tier Models
  'gemini-2.5-pro' => 'Gemini 2.5 Pro (Free Tier)',
  'gemini-2.5-flash' => 'Gemini 2.5 Flash (Free Tier)',
  'gemini-2.5-flash-preview-09-2025' => 'Gemini 2.5 Flash Preview (Free Tier)',
  'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash-Lite (Free Tier)',
  'gemini-2.5-flash-lite-preview-09-2025' => 'Gemini 2.5 Flash-Lite Preview (Free Tier)',
  'gemini-2.5-flash-native-audio-preview-09-2025' => 'Gemini 2.5 Flash Native Audio (Live API) (Free Tier)',
  'gemini-2.5-flash-image' => 'Gemini 2.5 Flash Image (Free Tier)',
  'gemini-2.5-flash-preview-tts' => 'Gemini 2.5 Flash Preview TTS (Free Tier)',
  'gemini-2.5-pro-preview-tts' => 'Gemini 2.5 Pro Preview TTS (Free Tier)',
  'gemini-2.5-computer-use-preview-10-2025' => 'Gemini 2.5 Computer Use Preview (Free Tier)',

  // ⚡ GEMINI 2.0 SERIES — Free Tier Models
  'gemini-2.0-flash' => 'Gemini 2.0 Flash (Free Tier)',
  'gemini-2.0-flash-lite' => 'Gemini 2.0 Flash-Lite (Free Tier)',

  // 🧠 ROBOTICS
  'gemini-robotics-er-1.5-preview' => 'Gemini Robotics-ER 1.5 Preview (Free Tier)',

  // 🖼️ IMAGEN MODELS (Image Generation)
  'imagen-4.0-generate-001' => 'Imagen 4 (Free Tier)',
  'imagen-4.0-fast-generate-001' => 'Imagen 4 Fast (Free Tier)',
  'imagen-4.0-ultra-generate-001' => 'Imagen 4 Ultra (Free Tier)',
  'imagen-3.0-generate-002' => 'Imagen 3 (Free Tier)',

  // 🎬 VEO MODELS (Video Generation)
  'veo-3.1-generate-preview' => 'Veo 3.1 (Free Tier)',
  'veo-3.1-fast-generate-preview' => 'Veo 3.1 Fast (Free Tier)',
  'veo-3.0-generate-001' => 'Veo 3 (Free Tier)',
  'veo-3.0-fast-generate-001' => 'Veo 3 Fast (Free Tier)',
  'veo-2.0-generate-001' => 'Veo 2 (Free Tier)',

  // 🔍 EMBEDDING MODEL
  'gemini-embedding-001' => 'Gemini Embedding (Free Tier)',

  // 💡 GEMMA SERIES (Open Models)
  'gemma-3' => 'Gemma 3 (Free Tier)',
  'gemma-3n' => 'Gemma 3n (Free Tier)',
];

    }

    public function countTokens(string $text): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return (int) ceil(strlen($text) / 4);
    }

    protected function convertMessagesToGeminiFormat(array $messages): array
    {
        $contents = [];

        foreach ($messages as $message) {
            $role = $message['role'] === 'assistant' ? 'model' : 'user';

            // Skip system messages or prepend to first user message
            if ($message['role'] === 'system') {
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [
                    ['text' => $message['content']],
                ],
            ];
        }

        return $contents;
    }
}
