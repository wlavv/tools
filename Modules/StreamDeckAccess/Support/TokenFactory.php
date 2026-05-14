<?php

namespace Modules\StreamDeckAccess\Support;

class TokenFactory
{
    public static function makePlainToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    public static function hash(string $plainToken): string
    {
        $key = (string) config('app.key', 'fallback-change-me');

        return hash_hmac('sha256', $plainToken, $key);
    }

    public static function hint(string $plainToken): string
    {
        return substr($plainToken, -6);
    }
}
