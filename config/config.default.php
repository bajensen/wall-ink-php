<?php
return [
    'cas' => [
        'enabled' => false,
    ],
    'settings' => [
        'displayErrorDetails' => true,
    ],
    'app_settings' => [
        'timezone' => 'America/Denver',
        'trusted_proxies' => [],
        'use_proxy_headers' => true,
    ],
    'wink_settings' => [
        'dither' => false,
        'refresh_seconds' => 30 * 60, // TODO: remove this setting
        'layouts' => [
            'setup' => ['class' => \Wink\Layout\Core\Setup::class],
            'error' => ['class' => \Wink\Layout\Core\Error::class],
            'builtin_reservable' => ['class' => \Wink\Layout\BuiltIn\Reservable::class],
            'builtin_event' => ['class' => \Wink\Layout\BuiltIn\Event::class],
        ],
        'sources' => [
            'random' => ['class' => \Wink\Source\Core\Random::class]
        ],
        'operation_schedule' => [
            'days' => [1, 2, 3, 4, 5, 6],
            'open' => '07:00:00',
            'close' => '23:00:00',
            'unavailable_message' => 'Not Reservable*',
            'available_message' => 'Available*'
        ]
    ]
];