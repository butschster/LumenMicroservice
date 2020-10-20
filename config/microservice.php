<?php

return [
    'name' => env('MICROSERVICE_NAME'),
    'version' => env('MICROSERVICE_VERSION', '1.0.0'),

    'jwt' => [

        /*
        |--------------------------------------------------------------------------
        | JWT Authentication Secret
        |--------------------------------------------------------------------------
        |
        | Note: This will be used for Symmetric algorithms only (HMAC),
        | since RSA and ECDSA use a private/public key combo (See below).
        |
        */
        'secret' => env('JWT_SECRET'),

        /*
        |--------------------------------------------------------------------------
        | JWT hashing algorithm
        |--------------------------------------------------------------------------
        |
        | Specify the hashing algorithm that will be used to sign the token.
        |
        | See here: https://github.com/namshi/jose/tree/master/src/Namshi/JOSE/Signer/OpenSSL
        | for possible values.
        |
        */
        'algo' => env('JWT_ALGO', 'HS256'),
    ]
];
