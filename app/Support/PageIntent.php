<?php

namespace App\Support;

use Illuminate\Support\Facades\Request;

class PageIntent
{
    /**
     * Resolve a hero keyword from a whitelisted `?intent=` query parameter.
     *
     * @param  array<string, string>  $map  intent slug => display keyword
     */
    public static function keyword(array $map, string $defaultKey): string
    {
        $intent = (string) Request::query('intent', '');

        return $map[$intent] ?? $map[$defaultKey];
    }
}
