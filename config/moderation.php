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
    
    'action' => [
        'status' => 'hidden', // Hide content from timeline
        'auto_report' => true, // Create a report entry in the database
    ],
];
