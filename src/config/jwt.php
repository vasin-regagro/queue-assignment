<?php

return [
    'secret' => env('JWT_SECRET', env('APP_KEY')),
    'issuer' => env('JWT_ISSUER', 'queue-assignment'),
    'ttl' => (int) env('JWT_TTL', 3600),
    'guest_ttl' => 86400,
];
