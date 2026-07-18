<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum messages per conversation
    |--------------------------------------------------------------------------
    |
    | When a conversation exceeds this many messages (user + assistant),
    | the frontend will be prompted to start a new conversation.
    |
    */
    'max_messages' => env('CHAT_MAX_MESSAGES', 40),

    /*
    |--------------------------------------------------------------------------
    | Maximum context tokens
    |--------------------------------------------------------------------------
    |
    | When the cumulative token count exceeds this limit, the conversation
    | context will be compressed via AI summary.
    |
    */
    'max_context_tokens' => env('CHAT_MAX_CONTEXT_TOKENS', 4000),

    /*
    |--------------------------------------------------------------------------
    | Recent messages to keep in context
    |--------------------------------------------------------------------------
    |
    | When compressing context via summary, this many recent messages
    | are retained alongside the summary.
    |
    */
    'context_recent_messages' => env('CHAT_CONTEXT_RECENT_MESSAGES', 15),

    /*
    |--------------------------------------------------------------------------
    | Maximum total tokens before forcing new conversation
    |--------------------------------------------------------------------------
    |
    | When total_tokens across all messages exceeds this, the API returns
    | requires_new_conversation = true.
    |
    */
    'max_total_tokens' => env('CHAT_MAX_TOTAL_TOKENS', 32000),

    /*
    |--------------------------------------------------------------------------
    | Default conversation title
    |--------------------------------------------------------------------------
    |
    | Title assigned to newly created conversations.
    |
    */
    'default_title' => env('CHAT_DEFAULT_TITLE', 'New Consultation'),

    /*
    |--------------------------------------------------------------------------
    | Minimum messages before doctor recommendation
    |--------------------------------------------------------------------------
    |
    | The minimum number of messages required before the "Find the Right Doctor"
    | button becomes available to the patient.
    |
    */
    'min_messages_for_recommendation' => env('CHAT_MIN_MESSAGES_FOR_RECOMMENDATION', 4),

    /*
    |--------------------------------------------------------------------------
    | Tiage confidence threshold
    |--------------------------------------------------------------------------
    |
    | The minimum confidence score required from the AI triage analysis
    | before the recommendation button is shown.
    |
    */
    'triage_confidence_threshold' => env('CHAT_TRIAGE_CONFIDENCE_THRESHOLD', 0.5),
];
