<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | Stripe price IDs come from the environment so each environment can
    | point at its own Stripe account/mode. AI credits are the monthly
    | allowance included in the plan; overage is metered via Stripe.
    |
    */

    'plans' => [
        'free' => [
            'name' => 'Free',
            'stripe_price_id' => env('STRIPE_PRICE_FREE'),
            'ai_credits_monthly' => 100,
            'sites' => 1,
        ],
        'pro' => [
            'name' => 'Pro',
            'stripe_price_id' => env('STRIPE_PRICE_PRO'),
            'ai_credits_monthly' => 2_000,
            'sites' => 5,
            'metered_overage_price_id' => env('STRIPE_PRICE_AI_OVERAGE'),
        ],
        'business' => [
            'name' => 'Business',
            'stripe_price_id' => env('STRIPE_PRICE_BUSINESS'),
            'ai_credits_monthly' => 10_000,
            'sites' => 25,
            'metered_overage_price_id' => env('STRIPE_PRICE_AI_OVERAGE'),
        ],
    ],

    'ai_overage_meter_event' => env('STRIPE_AI_OVERAGE_METER_EVENT', 'ai_credits'),
];
