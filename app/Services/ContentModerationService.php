<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ContentModerationService
{
    /**
     * Analyze text for toxicity using Rule-based and AI-based methods.
     *
     * @param string $text
     * @return object
     */
    public function analyze($text)
    {
        $result = (object) [
            'is_toxic' => false,
            'reason' => null,
            'technique' => null,
        ];

        if (!$text) {
            return $result;
        }

        // 1. Rule-based: Keyword Filter
        if (config('moderation.keywords.enabled')) {
            $bannedWords = config('moderation.keywords.banned_list', []);
            foreach ($bannedWords as $word) {
                if (Str::contains(Str::lower($text), Str::lower($word))) {
                    $result->is_toxic = true;
                    $result->reason = "Matches banned keyword: '{$word}'";
                    $result->technique = 'Rule-based';
                    return $result;
                }
            }
        }

        // 2. AI-based: OpenAI Moderation API
        if (config('moderation.ai_moderation.enabled') && config('moderation.ai_moderation.api_key')) {
            try {
                $response = Http::withToken(config('moderation.ai_moderation.api_key'))
                    ->post('https://api.openai.com/v1/moderations', [
                        'input' => $text,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if ($data['results'][0]['flagged'] ?? false) {
                        $result->is_toxic = true;
                        $result->reason = 'Flagged by AI: ' . implode(', ', array_keys(array_filter($data['results'][0]['categories'])));
                        $result->technique = 'AI-based';
                        return $result;
                    }
                }
            } catch (\Exception $e) {
                // Silently fail AI moderation to avoid blocking the app
                \Log::error('Moderation API Error: ' . $e->getMessage());
            }
        }

        return $result;
    }
}
