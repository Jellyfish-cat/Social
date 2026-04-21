<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Content Moderation Configuration
    |--------------------------------------------------------------------------
    */

    'enabled' => true,

    // Use OpenAI Moderation API for intelligent detection
    'ai_moderation' => [
        'enabled' => env('MODERATION_AI_ENABLED', false),
        'api_key' => env('OPENAI_API_KEY'),
    ],

    // Rule-based keyword filtering
    'keywords' => [
        'enabled' => true,
        // Common Vietnamese and English profanity
        'banned_list' => [
            'đm', 'đmm', 'vkl', 'vcl', 'đcm', 'đéo', 'dcm', 'địt', 'dit', 'fuck', 'shit', 'bitch', 'asshole',
            'ngu', 'cặc', 'cac', 'lồn', 'lon', 'buồi', 'buoi', 'cút', 'cut'
        ],
    ],

    // Action taken when toxic content is detected
    'action' => [
        'status' => 'hide', // Hide content from timeline
        'auto_report' => true, // Create a report entry in the database
    ],
];
