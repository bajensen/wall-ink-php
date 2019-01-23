<?php
return [
    'settings' => [
        'displayErrorDetails' => false,                 // Shut off error details in production
    ],
    'app_settings' => [
        'timezone' => 'America/Denver',                 // Put your timezone
        'trusted_proxies' => [],                        // Add IPs of upstream load balances/reverse proxies
    ],
    'wink_settings' => [
        'image_key' => 'hunter2',                       // Use your production image key
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=wink',// Your hostname and DB name
            'user' => 'wink',                           // Your Username
            'pass' => 'super secret password',          // Your Password
        ],
        'layouts' => [
            // Add custom layouts
            // 'my_custom_layout' => ['class' => \Wink\Layout\Custom\MyLayout::class],
        ],
        'sources' => [
            // Add custom sources
            // 'my_custom_source' => ['class' => \Wink\Source\Custom\MySource::class],
        ],
        'operation_schedule' => [
            'days' => [1, 2, 3, 4, 5, 6],               // 0 - Sunday, 6 - Saturday
            'open' => '07:00:00',                       // Time reservations become available. Use 24-hour format.
            'close' => '23:00:00',                      // Time reservations are no longer available. Use 24-hour format.
            'unavailable_message' => 'Not Reservable*', // The message to give when the time is out of the operating days/times
            'available_message' => 'Available*'         // The message to show when the time is open.
        ]
    ]
];