<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Connection System Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file controls various aspects of the user connection
    | system including notifications, messages, limits, and business rules.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Control which notifications are sent for different connection events
    | and their default message templates.
    |
    */
    'notifications' => [
        'enabled' => env('CONNECTIONS_NOTIFICATIONS_ENABLED', true),
        
        'events' => [
            'request_sent' => [
                'enabled' => env('CONNECTIONS_NOTIFY_REQUEST_SENT', true),
                'severity' => 'info',
                'title' => 'New Connection Request',
                'message_template' => ':sender_name sent you a connection request: ":message"',
                'notification_type' => 'connection_request',
            ],
            
            'request_accepted' => [
                'enabled' => env('CONNECTIONS_NOTIFY_REQUEST_ACCEPTED', true),
                'severity' => 'success',
                'title' => 'Connection Request Accepted',
                'message_template' => ':receiver_name accepted your connection request!',
                'notification_type' => 'connection_accepted',
            ],
            
            'request_declined' => [
                'enabled' => env('CONNECTIONS_NOTIFY_REQUEST_DECLINED', true),
                'severity' => 'warning',
                'title' => 'Connection Request Declined',
                'message_template' => ':receiver_name declined your connection request.',
                'notification_type' => 'connection_declined',
            ],
            
            'request_cancelled' => [
                'enabled' => env('CONNECTIONS_NOTIFY_REQUEST_CANCELLED', true),
                'severity' => 'info',
                'title' => 'Connection Request Cancelled',
                'message_template' => ':sender_name cancelled their connection request.',
                'notification_type' => 'connection_cancelled',
            ],
            
            'connection_removed' => [
                'enabled' => env('CONNECTIONS_NOTIFY_CONNECTION_REMOVED', true),
                'severity' => 'info',
                'title' => 'Connection Removed',
                'message_template' => ':user_name has removed you from their connections.',
                'notification_type' => 'connection_removed',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Message Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for connection request messages and validation rules.
    |
    */
    'messages' => [
        'required' => env('CONNECTIONS_MESSAGE_REQUIRED', true),
        'min_length' => env('CONNECTIONS_MESSAGE_MIN_LENGTH', 10),
        'max_length' => env('CONNECTIONS_MESSAGE_MAX_LENGTH', 500),
        'default_message' => env('CONNECTIONS_DEFAULT_MESSAGE', 'Hi! I would like to connect with you.'),
        
        'validation_messages' => [
            'required' => 'A message is required when sending a connection request.',
            'min' => 'Connection message must be at least :min characters long.',
            'max' => 'Connection message cannot exceed :max characters.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Control how many connection requests a user can send within certain
    | time periods to prevent spam and abuse.
    |
    */
    'rate_limits' => [
        'enabled' => env('CONNECTIONS_RATE_LIMITING_ENABLED', false),
        'max_requests_per_hour' => env('CONNECTIONS_MAX_REQUESTS_PER_HOUR', 10),
        'max_requests_per_day' => env('CONNECTIONS_MAX_REQUESTS_PER_DAY', 50),
        'max_requests_per_user' => env('CONNECTIONS_MAX_REQUESTS_PER_USER', 3), // To same user
        'cooldown_after_decline' => env('CONNECTIONS_COOLDOWN_AFTER_DECLINE', 7), // Days
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Rules
    |--------------------------------------------------------------------------
    |
    | Business rules and constraints for the connection system.
    |
    */
    'rules' => [
        'allow_self_connection' => env('CONNECTIONS_ALLOW_SELF_CONNECTION', false),
        'allow_duplicate_requests' => env('CONNECTIONS_ALLOW_DUPLICATE_REQUESTS', false),
        'allow_reconnection_after_removal' => env('CONNECTIONS_ALLOW_RECONNECTION_AFTER_REMOVAL', true),
        'auto_accept_reconnection' => env('CONNECTIONS_AUTO_ACCEPT_RECONNECTION', false),
        'require_mutual_connection' => env('CONNECTIONS_REQUIRE_MUTUAL_CONNECTION', false),
        'max_connections_per_user' => env('CONNECTIONS_MAX_CONNECTIONS_PER_USER', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Statuses
    |--------------------------------------------------------------------------
    |
    | Available connection statuses and their display information.
    |
    */
    'statuses' => [
        'pending' => [
            'label' => 'Pending',
            'description' => 'Connection request has been sent but not yet responded to',
            'color' => 'warning',
        ],
        'accepted' => [
            'label' => 'Connected',
            'description' => 'Connection request has been accepted',
            'color' => 'success',
        ],
        'declined' => [
            'label' => 'Declined',
            'description' => 'Connection request has been declined',
            'color' => 'danger',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'description' => 'Connection request has been cancelled by sender',
            'color' => 'secondary',
        ],
        'blocked' => [
            'label' => 'Blocked',
            'description' => 'Connection has been blocked',
            'color' => 'dark',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Privacy Settings
    |--------------------------------------------------------------------------
    |
    | Control privacy and visibility options for connections.
    |
    */
    'privacy' => [
        'show_mutual_connections' => env('CONNECTIONS_SHOW_MUTUAL_CONNECTIONS', true),
        'show_connection_count' => env('CONNECTIONS_SHOW_CONNECTION_COUNT', true),
        'allow_connection_export' => env('CONNECTIONS_ALLOW_CONNECTION_EXPORT', false),
        'require_approval_for_visibility' => env('CONNECTIONS_REQUIRE_APPROVAL_FOR_VISIBILITY', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Automatic cleanup of old connection records.
    |
    */
    'cleanup' => [
        'enabled' => env('CONNECTIONS_CLEANUP_ENABLED', true),
        'delete_declined_after_days' => env('CONNECTIONS_DELETE_DECLINED_AFTER_DAYS', 30),
        'delete_cancelled_after_days' => env('CONNECTIONS_DELETE_CANCELLED_AFTER_DAYS', 30),
        'archive_old_connections' => env('CONNECTIONS_ARCHIVE_OLD_CONNECTIONS', false),
        'archive_inactive_after_days' => env('CONNECTIONS_ARCHIVE_INACTIVE_AFTER_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    |
    | Settings for integrating with other system components.
    |
    */
    'integration' => [
        'broadcast_events' => env('CONNECTIONS_BROADCAST_EVENTS', true),
        'log_connection_activities' => env('CONNECTIONS_LOG_ACTIVITIES', true),
        'sync_with_external_systems' => env('CONNECTIONS_SYNC_EXTERNAL', false),
        'webhook_url' => env('CONNECTIONS_WEBHOOK_URL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | UI/UX Settings
    |--------------------------------------------------------------------------
    |
    | Frontend display and user experience settings.
    |
    */
    'ui' => [
        'pagination' => [
            'default_per_page' => env('CONNECTIONS_DEFAULT_PER_PAGE', 20),
            'max_per_page' => env('CONNECTIONS_MAX_PER_PAGE', 100),
        ],
        'sorting' => [
            'default_sort' => env('CONNECTIONS_DEFAULT_SORT', 'created_at'),
            'default_direction' => env('CONNECTIONS_DEFAULT_DIRECTION', 'desc'),
        ],
        'filters' => [
            'allow_status_filter' => env('CONNECTIONS_ALLOW_STATUS_FILTER', true),
            'allow_date_filter' => env('CONNECTIONS_ALLOW_DATE_FILTER', true),
            'allow_search' => env('CONNECTIONS_ALLOW_SEARCH', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    |
    | Advanced configuration options for power users.
    |
    */
    'advanced' => [
        'enable_connection_analytics' => env('CONNECTIONS_ENABLE_ANALYTICS', false),
        'enable_connection_recommendations' => env('CONNECTIONS_ENABLE_RECOMMENDATIONS', false),
        'enable_bulk_operations' => env('CONNECTIONS_ENABLE_BULK_OPERATIONS', false),
        'custom_connection_types' => env('CONNECTIONS_CUSTOM_TYPES', false),
        'enable_connection_notes' => env('CONNECTIONS_ENABLE_NOTES', false),
        'enable_connection_labels' => env('CONNECTIONS_ENABLE_LABELS', false),
    ],
]; 