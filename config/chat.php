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
];
