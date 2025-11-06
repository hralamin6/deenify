<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use League\CommonMark\CommonMarkConverter;

class OpenRouterService implements AiServiceInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://openrouter.ai/api/v1';
    protected string $defaultModel = 'minimax/minimax-m2:free';

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
    }

    public function chat(array $messages, array $options = []): array
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])
            ->timeout(120) // 2 minutes timeout
            ->connectTimeout(30) // 30 seconds connection timeout
            ->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['error']['message'] ?? $errorBody['message'] ?? $response->body();

                Log::error('OpenRouter API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error' => $errorMessage,
                ]);

                throw new \Exception($errorMessage);
            }

            $data = $response->json();
          $markdown = data_get($data, 'choices.0.message.content', '');
          //            dd($response);
          if ($markdown === '') {
            throw new \RuntimeException('Empty response from AI');
          }

          $converter = new CommonMarkConverter;
          $html = $converter->convert($markdown);
          $reply = (string) $html;
            return [
                'content' => $reply ?? '',
                'tokens' => $data['usage']['total_tokens'] ?? 0,
                'model' => $data['model'] ?? $model,
            ];
        } catch (\Exception $e) {
            Log::error('OpenRouter Service Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function streamChat(array $messages, array $options = []): \Generator
    {
        $model = $options['model'] ?? $this->defaultModel;
        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 2000;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
                'stream' => true,
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
                    if (empty($line) || $line === 'data: [DONE]') {
                        continue;
                    }

                    if (str_starts_with($line, 'data: ')) {
                        $json = substr($line, 6);
                        $data = json_decode($json, true);

                        if (isset($data['choices'][0]['delta']['content'])) {
                            yield $data['choices'][0]['delta']['content'];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('OpenRouter Stream Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function generateImage(string $prompt, array $options = []): string
    {
        throw new \Exception('Image generation not supported by OpenRouter. Use Pollinations service.');
    }

    public function getAvailableModels(): array
    {


      return [
        'minimax/minimax-m2:free' => 'MiniMax: MiniMax M2 (free)',
        'tngtech/deepseek-r1t2-chimera:free' => 'TNG: DeepSeek R1T2 Chimera (free)',
        'z-ai/glm-4.5-air:free' => 'Z.AI: GLM 4.5 Air (free)',
        'tngtech/deepseek-r1t-chimera:free' => 'TNG: DeepSeek R1T Chimera (free)',
        'deepseek/deepseek-chat-v3-0324:free' => 'DeepSeek: DeepSeek V3 0324 (free)',
        'deepseek/deepseek-r1-0528:free' => 'DeepSeek: R1 0528 (free)',
        'qwen/qwen3-235b-a22b:free' => 'Qwen: Qwen3 235B A22B (free)',
        'qwen/qwen3-coder:free' => 'Qwen: Qwen3 Coder 480B A35B (free)',
        'meta-llama/llama-3.3-70b-instruct:free' => 'Meta: Llama 3.3 70B Instruct (free)',
        'meituan/longcat-flash-chat:free' => 'Meituan: LongCat Flash Chat (free)',
        'deepseek/deepseek-r1:free' => 'DeepSeek: R1 (free)',
        'microsoft/mai-ds-r1:free' => 'Microsoft: MAI DS R1 (free)',
        'openai/gpt-oss-20b:free' => 'OpenAI: gpt-oss-20b (free)',
        'nvidia/nemotron-nano-12b-v2-vl:free' => 'NVIDIA: Nemotron Nano 12B 2 VL (free)',
        'google/gemma-3-27b-it:free' => 'Google: Gemma 3 27B (free)',
        'deepseek/deepseek-r1-distill-llama-70b:free' => 'DeepSeek: R1 Distill Llama 70B (free)',
        'meta-llama/llama-4-maverick:free' => 'Meta: Llama 4 Maverick (free)',
        'deepseek/deepseek-chat-v3.1:free' => 'DeepSeek: DeepSeek V3.1 (free)',
        'cognitivecomputations/dolphin-mistral-24b-venice-edition:free' => 'Venice: Uncensored (free)',
        'deepseek/deepseek-r1-0528-qwen3-8b:free' => 'DeepSeek: R1 0528 Qwen3 8B (free)',
        'mistralai/mistral-nemo:free' => 'Mistral: Mistral Nemo (free)',
        'alibaba/tongyi-deepresearch-30b-a3b:free' => 'Tongyi DeepResearch 30B A3B (free)',
        'mistralai/mistral-small-3.2-24b-instruct:free' => 'Mistral: Mistral Small 3.2 24B (free)',
        'mistralai/mistral-small-3.1-24b-instruct:free' => 'Mistral: Mistral Small 3.1 24B (free)',
        'qwen/qwen3-14b:free' => 'Qwen: Qwen3 14B (free)',
        'qwen/qwen3-30b-a3b:free' => 'Qwen: Qwen3 30B A3B (free)',
        'nousresearch/hermes-3-llama-3.1-405b:free' => 'Nous: Hermes 3 405B Instruct (free)',
        'mistralai/mistral-7b-instruct:free' => 'Mistral: Mistral 7B Instruct (free)',
        'nvidia/nemotron-nano-9b-v2:free' => 'NVIDIA: Nemotron Nano 9B V2 (free)',
        'meta-llama/llama-3.3-8b-instruct:free' => 'Meta: Llama 3.3 8B Instruct (free)',
        'meta-llama/llama-4-scout:free' => 'Meta: Llama 4 Scout (free)',
        'qwen/qwen2.5-vl-32b-instruct:free' => 'Qwen: Qwen2.5 VL 32B Instruct (free)',
        'qwen/qwen-2.5-coder-32b-instruct:free' => 'Qwen2.5 Coder 32B Instruct (free)',
        'qwen/qwen-2.5-72b-instruct:free' => 'Qwen2.5 72B Instruct (free)',
        'moonshotai/kimi-k2:free' => 'MoonshotAI: Kimi K2 0711 (free)',
        'mistralai/mistral-small-24b-instruct-2501:free' => 'Mistral: Mistral Small 3 (free)',
        'qwen/qwen3-4b:free' => 'Qwen: Qwen3 4B (free)',
        'meta-llama/llama-3.2-3b-instruct:free' => 'Meta: Llama 3.2 3B Instruct (free)',
        'google/gemma-3-4b-it:free' => 'Google: Gemma 3 4B (free)',
        'arliai/qwq-32b-arliai-rpr-v1:free' => 'ArliAI: QwQ 32B RpR v1 (free)',
        'google/gemma-3n-e2b-it:free' => 'Google: Gemma 3n 2B (free)',
        'google/gemma-3-12b-it:free' => 'Google: Gemma 3 12B (free)',
        'google/gemma-3n-e4b-it:free' => 'Google: Gemma 3n 4B (free)',
        'agentica-org/deepcoder-14b-preview:free' => 'Agentica: Deepcoder 14B Preview (free)',

      ];

    }

    public function countTokens(string $text): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return (int) ceil(strlen($text) / 4);
    }
}
