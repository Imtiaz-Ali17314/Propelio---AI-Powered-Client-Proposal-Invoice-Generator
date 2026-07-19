<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GroqService
{
    protected string $apiKey;
    protected string $model = 'llama-3.3-70b-versatile';
    protected string $baseUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
    }

    public function generateScope(string $brief): array
    {
        return $this->callGroq($this->scopePrompt($brief));
    }

    public function generateTimeline(string $brief, array $scope): array
    {
        return $this->callGroq($this->timelinePrompt($brief, $scope));
    }

    public function generateCostBreakdown(string $brief, array $scope, array $timeline): array
    {
        return $this->callGroq($this->costPrompt($brief, $scope, $timeline));
    }

    protected function callGroq(string $prompt): array
    {
        $response = Http::timeout(30)
            ->withToken($this->apiKey)
            ->post($this->baseUrl, [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that only responds with valid JSON, no markdown formatting, no explanations, no code fences.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
                'response_format' => ['type' => 'json_object'], // Groq bhi JSON mode support karta hai
            ]);

        if ($response->failed()) {
            Log::error('Groq API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('AI service failed: ' . $response->status() . ' - ' . $response->body());
        }

        $data = $response->json();
        $text = $data['choices'][0]['message']['content'] ?? null;

        if (!$text) {
            throw new Exception('AI response was empty or malformed.');
        }

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Groq JSON parse failed', ['raw' => $text]);
            throw new Exception('AI response could not be parsed as JSON.');
        }

        return $decoded;
    }

    /**
     * ===== PROMPT BUILDERS ===== (bilkul GeminiService jaisay hi)
     */

    protected function scopePrompt(string $brief): string
    {
        return <<<PROMPT
You are a professional business analyst helping a freelance agency write a client proposal.

Based on the following client brief, generate a project SCOPE in strict JSON format.

Client Brief:
"{$brief}"

Return ONLY valid JSON in exactly this structure:
{
  "overview": "A 2-3 sentence summary of the project",
  "deliverables": ["deliverable 1", "deliverable 2", "deliverable 3"],
  "out_of_scope": ["item not included 1", "item not included 2"]
}
PROMPT;
    }

    protected function timelinePrompt(string $brief, array $scope): string
    {
        $scopeJson = json_encode($scope);

        return <<<PROMPT
You are a professional project manager helping a freelance agency write a client proposal timeline.

Client Brief:
"{$brief}"

Already-defined Scope:
{$scopeJson}

Generate a realistic project TIMELINE in strict JSON format.

Return ONLY valid JSON in exactly this structure:
{
  "total_duration": "e.g. 6 weeks",
  "phases": [
    { "name": "Discovery & Planning", "duration": "1 week", "description": "short description" },
    { "name": "Design", "duration": "1.5 weeks", "description": "short description" }
  ]
}
PROMPT;
    }

    protected function costPrompt(string $brief, array $scope, array $timeline): string
    {
        $scopeJson = json_encode($scope);
        $timelineJson = json_encode($timeline);

        return <<<PROMPT
You are a professional agency pricing consultant helping write a client proposal.

Client Brief:
"{$brief}"

Scope:
{$scopeJson}

Timeline:
{$timelineJson}

Generate a realistic COST BREAKDOWN in strict JSON format, using USD as a reasonable freelance/agency market rate.

Return ONLY valid JSON in exactly this structure:
{
  "items": [
    { "label": "UI/UX Design", "amount": 800 },
    { "label": "Frontend Development", "amount": 1500 }
  ],
  "currency": "USD",
  "subtotal": 2300,
  "tax_percent": 0,
  "total": 2300
}
PROMPT;
    }
}