<?php

$sslOptions = [];
if (env('RABBITMQ_SSL')) {
    $sslOptions['verify_peer'] = true;
}

return [
    /*
    |--------------------------------------------------------------------------
    | Define which configuration should be used
    |--------------------------------------------------------------------------
    */

    'use' => 'production',

    /*
    |--------------------------------------------------------------------------
    | AMQP properties separated by key
    |--------------------------------------------------------------------------
    */

    'properties' => [
        'production' => [
            'host' => env('RABBITMQ_HOST', '127.0.0.1'),
            'port' => env('RABBITMQ_PORT', 5672),
            'username' => env('RABBITMQ_USERNAME'),
            'password' => env('RABBITMQ_PASSWORD'),
            'vhost' => env('RABBITMQ_VHOST', '/'),
            'connect_options' => [
                'read_write_timeout' => 30,    // needs to be at least 2x heartbeat
                'keepalive' => false, // doesn't work with ssl connections
                'heartbeat' => 15
            ],
            'ssl_connection' => env('RABBITMQ_SSL'),
            'ssl_options' => $sslOptions,

            'exchange' => 'amq.topic',
            'exchange_type' => 'topic',
            'exchange_passive' => false,
            'exchange_durable' => true,
            'exchange_auto_delete' => false,
            'exchange_internal' => false,
            'exchange_nowait' => false,
            'exchange_properties' => [],

            'queue_force_declare' => false,
            'queue_passive' => false,
            'queue_durable' => true,
            'queue_exclusive' => false,
            'queue_auto_delete' => false,
            'queue_nowait' => false,
            'queue_properties' => [
                'x-ha-policy' => [
                    'S',
                    'all',
                ],
            ],

            'consumer_tag' => '',
            'consumer_no_local' => false,
            'consumer_no_ack' => false,
            'consumer_exclusive' => false,
            'consumer_nowait' => false,
            'timeout' => 10,
            'persistent' => true,
        ],
    ],
];
