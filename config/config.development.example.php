<?php
return [
    'app_settings' => [
        'timezone' => 'America/Denver',                     // Your timezone
    ],
    'wink_settings' => [
        'image_key' => 'hunter2',                           // Update to your development image key
        'db' => [
            'dsn' => 'mysql:host=localhost;dbname=wink',    // Update host= and dbname= to your proper hostname and DB name
            'user' => 'wink',                               // Your MySQL username
            'pass' => 'super secret password',              // Your MySQL password
        ],
    ]
];