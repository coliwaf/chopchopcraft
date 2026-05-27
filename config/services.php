<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */
 
    // ─── M-Pesa (Safaricom Daraja API) ───────────────────────────────────────
    // Sandbox portal : https://developer.safaricom.co.ke
    // Production     : https://developer.safaricom.co.ke (switch env to live)
    'mpesa' => [
        'consumer_key'    => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode'       => env('MPESA_SHORTCODE'),        // PayBill or Till number
        'passkey'         => env('MPESA_PASSKEY'),          // from Daraja dashboard
        'callback_url'    => env('MPESA_CALLBACK_URL'),     // must be public HTTPS URL
        'env'             => env('MPESA_ENV', 'sandbox'),   // 'sandbox' or 'production'
    ],
 
    // ─── Stripe ───────────────────────────────────────────────────────────────
    // Dashboard : https://dashboard.stripe.com
    // Keys      : Developers → API keys
    // Webhooks  : Developers → Webhooks → Add endpoint → /webhooks/stripe
    //             Events to listen: payment_intent.succeeded, payment_intent.payment_failed, charge.refunded
    'stripe' => [
        'key'            => env('STRIPE_KEY'),              // pk_test_... or pk_live_...
        'secret'         => env('STRIPE_SECRET'),           // sk_test_... or sk_live_...
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),   // whsec_... from webhook dashboard
        'currency'       => env('STRIPE_CURRENCY', 'KES'),
    ],
 
    // ─── PayPal ───────────────────────────────────────────────────────────────
    // Dashboard : https://developer.paypal.com/developer/applications
    // Sandbox   : use sandbox credentials for local dev
    // Webhooks  : My Apps → App → Webhooks → Add Webhook → /webhooks/paypal
    //             Events: PAYMENT.CAPTURE.COMPLETED, PAYMENT.CAPTURE.DENIED, PAYMENT.CAPTURE.REFUNDED
    'paypal' => [
        'client_id'     => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'webhook_id'    => env('PAYPAL_WEBHOOK_ID'),        // from PayPal webhook dashboard
        'mode'          => env('PAYPAL_MODE', 'sandbox'),   // 'sandbox' or 'live'
        'currency'      => env('PAYPAL_CURRENCY', 'USD'),   // PayPal KES not supported — use USD
    ],
 
    // ─── WhatsApp Cloud API (Meta) ────────────────────────────────────────────
    // Setup     : https://developers.facebook.com → My Apps → WhatsApp → Getting Started
    // Token     : Temporary token (dev) or permanent System User token (prod)
    // Templates : WhatsApp Manager → Message Templates (must be approved before use)
    'whatsapp' => [
        'token'             => env('WHATSAPP_TOKEN'),           // Bearer token
        'phone_number_id'   => env('WHATSAPP_PHONE_NUMBER_ID'), // from Meta dashboard
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'business_number'   => env('WHATSAPP_BUSINESS_NUMBER', '254700000000'), // shown on WA buttons
        'api_version'       => env('WHATSAPP_API_VERSION', 'v19.0'),
    ],

    // ─── Default services ────────────────────────────────────────────

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
