<?php

namespace App\Http\Controllers;

use App\Services\MinimaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MinimaxController extends Controller
{
    protected $minimaxService;

    public function __construct(MinimaxService $minimaxService)
    {
        $this->minimaxService = $minimaxService;
    }

    /**
     * Call a function using Minimax AI service
     */
    public function callFunction(Request $request)
    {
        $request->validate([
            'function_name' => 'required|string',
            'parameters' => 'nullable|array',
            'model' => 'nullable|string|in:abab6.5s-chat,abab6.5-chat,abab6.5g-chat'
        ]);

        try {
            $functionName = $request->input('function_name');
            $parameters = $request->input('parameters', []);
            $model = $request->input('model', 'abab6.5s-chat');

            $result = $this->minimaxService->callFunction($functionName, $parameters, $model);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Minimax function call failed', [
                'user_id' => Auth::id(),
                'function_name' => $request->input('function_name'),
                'parameters' => $request->input('parameters'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to call function: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate text completion
     */
    public function generateText(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'model' => 'nullable|string|in:abab6.5s-chat,abab6.5-chat,abab6.5g-chat'
        ]);

        try {
            $prompt = $request->input('prompt');
            $model = $request->input('model', 'abab6.5s-chat');

            $result = $this->minimaxService->generateText($prompt, $model);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Minimax text generation failed', [
                'user_id' => Auth::id(),
                'prompt' => $request->input('prompt'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate text: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate image from text prompt
     */
    public function generateImage(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
            'model' => 'nullable|string|in:abab6.5-chat'
        ]);

        try {
            $prompt = $request->input('prompt');
            $model = $request->input('model', 'abab6.5-chat');

            $result = $this->minimaxService->generateImage($prompt, ['model' => $model]);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Minimax image generation failed', [
                'user_id' => Auth::id(),
                'prompt' => $request->input('prompt'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check Minimax API connection
     */
    public function checkConnection()
    {
        try {
            $isConnected = $this->minimaxService->checkConnection();
            $availableModels = $this->minimaxService->getAvailableModels();

            return response()->json([
                'success' => true,
                'connected' => $isConnected,
                'available_models' => $availableModels
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'connected' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate embeddings
     */
    public function generateEmbeddings(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'model' => 'nullable|string|in:text-embedding-01'
        ]);

        try {
            $text = $request->input('text');
            $model = $request->input('model', 'text-embedding-01');

            $result = $this->minimaxService->generateEmbeddings($text, $model);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Minimax embeddings generation failed', [
                'user_id' => Auth::id(),
                'text_length' => strlen($request->input('text')),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate embeddings: ' . $e->getMessage()
            ], 500);
        }
    }
}