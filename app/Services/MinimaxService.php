<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MinimaxService
{
    protected $baseUrl;
    protected $apiKey;
    protected $groupId;

    public function __construct()
    {
        $this->baseUrl = config('services.minimax.base_url', 'https://api.minimax.chat/v1');
        $this->apiKey = config('services.minimax.api_key');
        $this->groupId = config('services.minimax.group_id');
        
        if (!$this->apiKey) {
            Log::warning('Minimax API key not configured');
        }
    }

    /**
     * Generate a unique tool ID for function calls
     */
    protected function generateToolId(): string
    {
        return 'call_function_' . Str::random(32) . '_' . time();
    }

    /**
     * Make API request with proper error handling
     */
    protected function makeRequest(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        if (!$this->apiKey) {
            throw new \Exception('Minimax API key not configured. Please check your .env file.');
        }

        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');
        
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->{$method}($url, $data);

            $responseData = $response->json();

            if ($response->failed()) {
                Log::error('Minimax API request failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $responseData,
                    'data' => $data
                ]);
                
                throw new \Exception($responseData['message'] ?? 'Minimax API request failed');
            }

            return $responseData;

        } catch (\Exception $e) {
            Log::error('Minimax API error: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'data' => $data,
                'exception' => $e
            ]);
            
            throw $e;
        }
    }

    /**
     * Call a function/tool with proper parameter handling
     */
    public function callFunction(string $functionName, array $parameters = [], string $model = 'abab6.5s-chat'): array
    {
        $toolId = $this->generateToolId();
        
        $payload = [
            'model' => $model,
            'tool' => [
                'type' => 'function',
                'function' => [
                    'name' => $functionName,
                    'parameters' => $parameters
                ]
            ],
            'messages' => [
                [
                    'role' => 'user',
                    'content' => 'Please call the function ' . $functionName . ' with these parameters: ' . json_encode($parameters)
                ]
            ],
            'tool_id' => $toolId
        ];

        return $this->makeRequest('text/chatcompletion_v2', $payload);
    }

    /**
     * Generate text completion
     */
    public function generateText(string $prompt, string $model = 'abab6.5s-chat', array $options = []): array
    {
        $payload = array_merge([
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'stream' => false
        ], $options);

        return $this->makeRequest('text/chatcompletion_v2', $payload);
    }

    /**
     * Generate embeddings
     */
    public function generateEmbeddings(string $text, string $model = 'text-embedding-01'): array
    {
        $payload = [
            'model' => $model,
            'text' => $text
        ];

        return $this->makeRequest('text/embedding', $payload);
    }

    /**
     * Generate image with text prompt
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        $payload = array_merge([
            'prompt' => $prompt,
            'model' => 'abab6.5-chat',
            'stream' => false
        ], $options);

        return $this->makeRequest('text_to_image', $payload);
    }

    /**
     * Check API connection and credentials
     */
    public function checkConnection(): bool
    {
        try {
            // Simple test to check if API key works
            $response = $this->makeRequest('text/chatcompletion_v2', [
                'model' => 'abab6.5s-chat',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Hello'
                    ]
                ],
                'max_tokens' => 10
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Minimax connection check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available models
     */
    public function getAvailableModels(): array
    {
        return [
            'chat' => [
                'abab6.5s-chat',
                'abab6.5-chat',
                'abab6.5g-chat'
            ],
            'embedding' => [
                'text-embedding-01'
            ],
            'image' => [
                'abab6.5-chat'
            ]
        ];
    }
}