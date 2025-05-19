<?php

return [
    'client_id' => env('PLAID_CLIENT_ID'),
    'environment' => env('PLAID_ENVIRONMENT', 'sandbox'),

    // Dynamically construct the secret key based on the environment
    // It will look for PLAID_SECRET_SANDBOX, PLAID_SECRET_DEVELOPMENT, etc.
    'secret' => env('PLAID_SECRET_' . strtoupper(env('PLAID_ENVIRONMENT', 'sandbox'))),

    // For Plaid Link initialization
    // Ensure your .env PLAID_COUNTRY_CODES and PLAID_PRODUCTS are comma-separated strings if they contain multiple values
    'country_codes' => explode(',', env('PLAID_COUNTRY_CODES', 'US')),
    'language' => env('PLAID_LANGUAGE', 'en'),
    'products' => explode(',', env('PLAID_PRODUCTS', 'auth')),

    // Adding webhook URL and redirect URI
    'webhook_url' => env('PLAID_WEBHOOK_URL', null),
    'redirect_uri' => env('PLAID_REDIRECT_URI', null),
];