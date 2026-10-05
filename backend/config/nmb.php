<?php

return [
    /*
    | NMB Bank Open Bank Project (OBP) sandbox
    | Docs: https://obp-apiexplorer-sandbox.nmbbank.co.tz/resource-docs/OBPv6.0.0
    | Host: https://obp-api-sandbox.nmbbank.co.tz
    */
    'base_url' => env('NMB_OBP_BASE_URL', 'https://obp-api-sandbox.nmbbank.co.tz'),
    'api_version' => env('NMB_OBP_API_VERSION', 'v5.0.0'),
    'bank_id' => env('NMB_OBP_BANK_ID', 'nmbb.01.tz.nmbb'),

    // Register consumer at NMB OBP sandbox, then set these in .env
    'consumer_id' => env('NMB_OBP_CONSUMER_ID', ''),
    'consumer_key' => env('NMB_OBP_CONSUMER_KEY', ''),
    'consumer_secret' => env('NMB_OBP_CONSUMER_SECRET', ''),
    'app_name' => env('NMB_OBP_APP_NAME', 'Emish Store'),
    'redirect_url' => env('NMB_OBP_REDIRECT_URL', 'https://emish.store/api/nmb/oauth/callback'),
    'username' => env('NMB_OBP_USERNAME', ''),
    'password' => env('NMB_OBP_PASSWORD', ''),

    // Default view for account operations
    'view_id' => env('NMB_OBP_VIEW_ID', 'owner'),

    // When true or credentials missing, return deterministic sandbox fixtures
    'mock' => env('NMB_OBP_MOCK', true),

    'timeout' => (int) env('NMB_OBP_TIMEOUT', 30),

    // Platform operating / escrow account on NMB OBP
    'escrow_account_id' => env('NMB_OBP_ESCROW_ACCOUNT_ID', 'mkulima-escrow-001'),

    // Policy: withdrawals >= this TZS require NMB link
    'withdrawal_link_threshold' => (float) env('NMB_WITHDRAWAL_LINK_THRESHOLD', 50000),
    // Policy: require verified link before loan disburse
    'require_verified_for_disburse' => filter_var(env('NMB_REQUIRE_VERIFIED_DISBURSE', false), FILTER_VALIDATE_BOOLEAN),
];
