<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class TaskAnalysisController extends Controller
{
    public function analyze(Request $request): JsonResponse
    {
        $taskDescription = $request->input('description');
        $taskComments = $request->input('comments', []);

        $taskText = "Analyze the following task and return a JSON object with: 'progress', 'completed tasks', 'pending tasks', 'open questions', 'estimated time to complete'.\n\nTask Description: " . $taskDescription . "\n\nComments:\n" . implode("\n", $taskComments);

        $analysis = $this->callDeepSeek($taskText);

        if (isset($analysis['error'])) {
            return response()->json($analysis, 500);
        }

        return response()->json($analysis);
    }

    private function callDeepSeek(string $taskText): array
    {
        $apiKey = env('DEEPSEEK_API_KEY');
        $endpoint = 'https://api.deepseek.com/chat/completions'; 
        $model = 'deepseek-chat';

        if (!$apiKey) {
            return ['error' => true, 'message' => 'API key is missing in environment variables.'];
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant that returns only valid JSON based on user instructions.'],
                ['role' => 'user', 'content' => $taskText]
            ],
            'response_format' => ['type' => 'json_object'],
            'stream' => false,
        ]);

        if ($response->failed()) {
            return [
                'error' => true,
                'message' => 'API request failed: ' . $response->body(),
            ];
        }

        $responseData = $response->json();
        $content = $responseData['choices'][0]['message']['content'] ?? null;

        if ($content && is_string($content)) {
            return json_decode($content, true);
        }

        return ['error' => true, 'message' => 'No valid JSON content found in API response.'];
    }
}
