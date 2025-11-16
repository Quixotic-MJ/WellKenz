<?php

namespace App\Console\Commands;

use App\Services\MinimaxService;
use Illuminate\Console\Command;

class TestMinimax extends Command
{
    protected $signature = 'minimax:test {action : The action to perform (test|function|generate)}';
    protected $description = 'Test Minimax AI service integration';

    public function handle(MinimaxService $minimaxService)
    {
        $action = $this->argument('action');

        $this->info('Testing Minimax AI Service Integration...');
        $this->newLine();

        try {
            switch ($action) {
                case 'test':
                    $this->testConnection($minimaxService);
                    break;
                case 'function':
                    $this->testFunctionCall($minimaxService);
                    break;
                case 'generate':
                    $this->testTextGeneration($minimaxService);
                    break;
                default:
                    $this->error('Invalid action. Use: test, function, or generate');
                    return 1;
            }
        } catch (\Exception $e) {
            $this->error('Minimax test failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function testConnection(MinimaxService $minimaxService)
    {
        $this->info('Testing API connection...');
        
        if ($minimaxService->checkConnection()) {
            $this->info('✅ Connection successful!');
            
            $models = $minimaxService->getAvailableModels();
            $this->info('Available models:');
            foreach ($models as $type => $modelList) {
                $this->line("  - $type: " . implode(', ', $modelList));
            }
        } else {
            $this->error('❌ Connection failed. Check your API key and configuration.');
        }
    }

    private function testFunctionCall(MinimaxService $minimaxService)
    {
        $this->info('Testing function call...');
        
        $parameters = [
            'numbers' => [1, 2, 3, 4, 5]
        ];
        
        $result = $minimaxService->callFunction('calculate_average', $parameters);
        
        $this->info('✅ Function call successful!');
        $this->line('Response: ' . json_encode($result, JSON_PRETTY_PRINT));
    }

    private function testTextGeneration(MinimaxService $minimaxService)
    {
        $this->info('Testing text generation...');
        
        $prompt = 'Explain what Laravel is in one sentence.';
        $result = $minimaxService->generateText($prompt);
        
        $this->info('✅ Text generation successful!');
        $this->line('Prompt: ' . $prompt);
        $this->line('Response: ' . json_encode($result, JSON_PRETTY_PRINT));
    }
}