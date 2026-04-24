<?php

return [
    // Support both names (spec + existing env)
    'api_token' => env('WHATSAPP_ACCESS_TOKEN', env('WHATSAPP_API_TOKEN')),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    'api_version' => env('WHATSAPP_GRAPH_VERSION', env('WHATSAPP_API_VERSION', 'v21.0')),
];
