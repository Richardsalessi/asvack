<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Aquí se almacenan las credenciales para servicios de terceros como
    | Mailgun, Postmark, AWS y ePayco. Este archivo actúa como un punto
    | central para configurar y acceder a estos servicios desde tu app.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ePayco Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Configuración centralizada para la pasarela de pagos ePayco.
    | Estas claves se cargan desde el archivo .env para mantener la
    | seguridad y facilidad de mantenimiento del proyecto.
    |
    */

    'epayco' => [
        'public_key'        => env('EPAYCO_PUBLIC_KEY'),
        'private_key'       => env('EPAYCO_PRIVATE_KEY'),
        'p_cust_id_cliente' => env('EPAYCO_P_CUST_ID_CLIENTE'),
        'p_key'             => env('EPAYCO_P_KEY'),
        'currency'          => env('EPAYCO_CURRENCY', 'COP'),
        'lang'              => env('EPAYCO_LANG', 'ES'),
        'test'              => (bool) env('EPAYCO_TEST', true),
    ],

];
