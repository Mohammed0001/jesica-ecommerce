<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * A short code that identifies one failed request.
 *
 * The same code is written into the log line and shown to the visitor, so a
 * customer saying "I got error TZ7K2QP9" points support at exactly one entry
 * without any internals leaking onto the page.
 */
class ErrorReference
{
    private const KEY = 'error.reference';

    /**
     * Get (creating once per request) the reference for the current request
     */
    public static function current(): string
    {
        $container = app();

        if (!$container->bound(self::KEY)) {
            $container->instance(self::KEY, strtoupper(Str::random(8)));
        }

        return $container->make(self::KEY);
    }
}
