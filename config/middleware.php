<?php

return [
    'api_token' => env('APP_ENV') === 'production'
        ? env('MIDDLEWARE_API_TOKEN')
        : env('MIDDLEWARE_API_TOKEN_TEST'),
];
