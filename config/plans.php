<?php

declare(strict_types=1);

return [

    'free' => [
        'name' => 'Free',
        'tag' => null,
        'highlighted' => false,
        'price' => ['monthly' => 0, 'annual' => 0],
        'stripe_price' => ['monthly' => null, 'annual' => null],
        'sub' => 'Everything you need for the everyday running of your home.',
        'cta' => 'Create your household',
        'note' => 'Perfect for getting your household organised.',
        'features' => [
            ['text' => 'Meal planner and recipe library', 'included' => true],
            ['text' => 'Shared household calendar', 'included' => true],
            ['text' => 'Shopping lists', 'included' => true],
            ['text' => 'Chores and streaks', 'included' => true],
            ['text' => 'Up to 2 household members', 'included' => true],
            ['text' => 'Household dashboard', 'included' => true],
        ],
    ],

    'home' => [
        'name' => 'Home',
        'tag' => 'MOST POPULAR',
        'highlighted' => true,
        'price' => ['monthly' => 6.99, 'annual' => 69.99],
        'stripe_price' => [
            'monthly' => env('STRIPE_PRICE_HOME_MONTHLY'),
            'annual' => env('STRIPE_PRICE_HOME_ANNUAL'),
        ],
        'sub' => 'Run your whole household together, with everything in one place.',
        'cta' => 'Start free, upgrade anytime',
        'note' => 'No card required. Cancel anytime.',
        'features' => [
            ['text' => 'Everything in Free', 'included' => true],
            ['text' => 'Unlimited household members', 'included' => true],
            ['text' => 'Unlimited shopping lists', 'included' => true],
            ['text' => 'Document vault with 5GB storage', 'included' => true],
            ['text' => 'Home budgeting', 'included' => true],
            ['text' => 'Basic budget insights', 'included' => true],
            ['text' => '2 AI meal plans per month', 'included' => true],
        ],
    ],

    'home_plus' => [
        'name' => 'Home Plus',
        'tag' => 'PREMIUM',
        'highlighted' => false,
        'price' => ['monthly' => 12.99, 'annual' => 129.99],
        'stripe_price' => [
            'monthly' => env('STRIPE_PRICE_HOME_PLUS_MONTHLY'),
            'annual' => env('STRIPE_PRICE_HOME_PLUS_ANNUAL'),
        ],
        'sub' => 'Let your home do more of the work.',
        'cta' => 'Choose Home Plus',
        'note' => 'For larger households, multiple properties and people who want the admin to take care of itself.',
        'features' => [
            ['text' => 'Everything in Home', 'included' => true],
            ['text' => 'Multiple properties under one login', 'included' => true],
            ['text' => '25GB document storage', 'included' => true],
            ['text' => 'Receipt/document scanning', 'included' => true],
            ['text' => 'AI-powered household insights', 'included' => true],
            ['text' => 'AI meal planning and shopping assistance', 'included' => true],
            ['text' => 'Export everything as PDF or CSV', 'included' => true],
            ['text' => 'Guest access for cleaners, sitters or other helpers', 'included' => true],
            ['text' => 'Priority human support', 'included' => true],
        ],
    ],

];
