<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Recommendation System Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration controls the user recommendation algorithm that 
    | powers the "You may also like" feature for finding potential contacts
    | and networking opportunities based on various similarity factors.
    |
    */

    'users' => [
        /*
        |--------------------------------------------------------------------------
        | Default Recommendation Factors
        |--------------------------------------------------------------------------
        |
        | These are the default factors used to calculate user similarity
        | scores. Each factor can be individually enabled/disabled and weighted.
        | Higher weights give more importance to that factor in the final score.
        |
        */
        'default_factors' => [
            // Users in complementary or related business roles
            'role_compatibility' => [
                'enabled' => true,
                'weight' => 3,
                'description' => 'Users in compatible business roles'
            ],

            // Users in the same geographic location
            'geographic_proximity' => [
                'enabled' => true,
                'weight' => 2,
                'description' => 'Users in the same geographic area'
            ],

            // Users working in similar business sectors/industries
            'industry_alignment' => [
                'enabled' => true,
                'weight' => 2,
                'description' => 'Users in similar business industries'
            ],

            // Users connected through mutual network connections
            'network_connections' => [
                'enabled' => true,
                'weight' => 3,
                'description' => 'Users with mutual connections in your network'
            ]
        ],

        /*
        |--------------------------------------------------------------------------
        | Recommendation Limits and Thresholds
        |--------------------------------------------------------------------------
        |
        | Configure default limits and scoring thresholds for recommendations.
        |
        */
        'default_limit' => 10,
        'max_limit' => 50,
        'min_score_threshold' => 3,

        /*
        |--------------------------------------------------------------------------
        | Role-Based User Targeting
        |--------------------------------------------------------------------------
        |
        | Define which types of users each role should primarily connect with
        | for networking and business purposes.
        |
        */
        'role_targeting' => [
            'exhibitor' => ['buyer', 'attendee', 'sponsor'],
            'buyer' => ['exhibitor', 'speaker'],
            'attendee' => ['speaker', 'exhibitor', 'sponsor'],
            'speaker' => ['attendee', 'sponsor', 'exhibitor'],
            'sponsor' => ['exhibitor', 'speaker', 'attendee'],
            'admin' => ['exhibitor', 'buyer', 'attendee', 'speaker', 'sponsor']
        ],

        /*
        |--------------------------------------------------------------------------
        | Scoring Weights
        |--------------------------------------------------------------------------
        |
        | Point values assigned for different types of matches.
        |
        */
        'scoring' => [
            'role_compatibility_points' => 3,
            'geographic_match_points' => 2,
            'industry_match_points' => 2,
            'mutual_connection_points' => 3,
            'same_country_bonus' => 1,
            'multiple_connection_bonus' => 1
        ],

        /*
        |--------------------------------------------------------------------------
        | Filtering Options
        |--------------------------------------------------------------------------
        |
        | Configure what should be excluded from recommendations.
        |
        */
        'filtering' => [
            'exclude_self' => true,
            'exclude_existing_connections' => false,
            'require_active_users' => true,
            'require_completed_profiles' => true
        ]
    ]
]; 