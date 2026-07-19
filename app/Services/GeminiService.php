<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GeminiService
{
    protected string $apiKey;
    protected string $model = 'gemini-1.5-flash';
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    /**
     * Scope generate karo client brief se
     */
    public function generateScope(string $brief): array
    {
        $prompt = $this->scopePrompt($brief);
        return $this->callGemini($prompt);
    }

    /**
     * Timeline generate karo brief + already generated scope se
     */
    public function generateTimeline(string $brief, array $scope): array
    {
        $prompt = $this->timelinePrompt($brief, $scope);
        return $this->callGemini($prompt);
    }

    /**
     * Cost breakdown generate karo brief + scope + timeline se
     */
    public function generateCostBreakdown(string $brief, array $scope, array $timeline): array
    {
        $prompt = $this->costPrompt($brief, $scope, $timeline);
        return $this->callGemini($prompt);
    }

    /**
     * Core method: Gemini ko call karo aur JSON response parse karo
     */
    protected function callGemini(string $prompt): array
    {
        $response = Http::timeout(30)->post(
            "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}",
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'responseMimeType' => 'application/json', // Gemini ko force karta hai pure JSON return karne ke liye
                ],
            ]
        );

        if ($response->failed()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new Exception('AI service failed: ' . $response->status() . ' - ' . $response->body());
        }

        $data = $response->json();

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$text) {
            throw new Exception('AI response was empty or malformed.');
        }

        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Gemini JSON parse failed', ['raw' => $text]);
            throw new Exception('AI response could not be parsed as JSON.');
        }

        return $decoded;
    }

    /**
     * ===== PROMPT BUILDERS =====
     */

    protected function scopePrompt(string $brief): string
    {
        return <<<PROMPT
You are a professional business analyst helping a freelance agency write a client proposal.

Based on the following client brief, generate a project SCOPE in strict JSON format.

Client Brief:
"{$brief}"

Return ONLY valid JSON in exactly this structure (no markdown, no extra text):
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

Based on this, generate a realistic project TIMELINE in strict JSON format.

Return ONLY valid JSON in exactly this structure (no markdown, no extra text):
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

Based on this, generate a realistic COST BREAKDOWN in strict JSON format, using USD as a reasonable freelance/agency market rate.

Return ONLY valid JSON in exactly this structure (no markdown, no extra text):
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