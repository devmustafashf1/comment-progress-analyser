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

        // Enriched prompt
        $taskText = "You are a project management assistant. Analyze the following task in full context, including all descriptions and comments. " .
            "Return a JSON object with the following fields:\n" .
            "1. 'progress': Overall progress as a percentage (e.g., '75%').\n" .
            "2. 'completed_tasks': List of tasks that have already been completed.\n" .
            "3. 'pending_tasks': List of tasks that are not yet done.\n" .
            "4. 'open_questions': Any unresolved questions or decisions needed.\n" .
            "5. 'estimated_time_to_complete': Rough estimate of remaining time.\n\n" .
            "Use all information provided below and base your analysis on the full context.\n\n" .
            "Task Description: " . $taskDescription . "\n\n" .
            "Comments:\n" . implode("\n", $taskComments) . "\n\n" .
            "Make sure to return only **valid JSON**.";

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
                ['role' => 'system', 'content' => 'You are a precise assistant that returns **only valid JSON** with required fields.'],
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
