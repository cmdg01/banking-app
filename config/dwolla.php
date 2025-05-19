<?php

// config/dwolla.php

return [
    'key' => env('DWOLLA_KEY'),
    'secret' => env('DWOLLA_SECRET'),
    'environment' => env('DWOLLA_ENVIRONMENT', 'sandbox'), // 'sandbox' or 'production'

    // Optional: Webhook secret for verifying Dwolla webhooks
    'webhook_secret' => env('DWOLLA_WEBHOOK_SECRET'),

    // Optional: Define account ID if you have a master Dwolla account that facilitates transfers
    'master_account_id' => env('DWOLLA_MASTER_ACCOUNT_ID'),
];

# .env.example (relevant Dwolla parts)
# Ensure these are added to your actual .env file with your credentials



# DWOLLA_WEBHOOK_SECRET=your_dwolla_webhook_secret # For webhook verification (later phase)
# DWOLLA_MASTER_ACCOUNT_ID=your_dwolla_master_account_id # If applicable
