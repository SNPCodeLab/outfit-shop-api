<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Hash Driver
    |--------------------------------------------------------------------------
    |
    | The "fallback" driver probes the runtime at construction and uses the
    | first algorithm (bcrypt, argon2id, argon2i) whose hash AND verify
    | round-trip works - required because the Vercel PHP runtime can verify
    | bcrypt but cannot create it ("Bcrypt hashing not supported").
    | Verification is format-agnostic, so existing bcrypt accounts keep
    | logging in whichever algorithm is selected.
    |
    | Supported: "fallback", "bcrypt", "argon", "argon2id"
    |
    */

    'driver' => env('HASH_DRIVER', 'fallback'),

    /*
    |--------------------------------------------------------------------------
    | Bcrypt Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Bcrypt algorithm. This will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'bcrypt' => [
        'rounds' => max(4, min(31, (int) (env('BCRYPT_ROUNDS', 12) ?: 12))),
        'verify' => env('HASH_VERIFY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Argon Options
    |--------------------------------------------------------------------------
    |
    | Here you may specify the configuration options that should be used when
    | passwords are hashed using the Argon algorithm. These will allow you
    | to control the amount of time it takes to hash the given password.
    |
    */

    'argon' => [
        'memory' => env('ARGON_MEMORY', 65536),
        'threads' => env('ARGON_THREADS', 1),
        'time' => env('ARGON_TIME', 4),
        'verify' => env('HASH_VERIFY', true),
    ],

];
