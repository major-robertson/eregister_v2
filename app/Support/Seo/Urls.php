<?php

namespace App\Support\Seo;

/**
 * Absolute URLs for the site's SEO identity (canonical, og:url, sitemap
 * <loc>, Organization @id), built from the configured app URL rather than
 * the request. Built from the request, a copy served at "/index.php/llc" or
 * on another host would canonicalise to itself. Ordinary links keep using
 * url()/route() so Herd Share tunnels still work.
 */
final class Urls
{
    /** "/" and "" give the root with a trailing slash, matching how the home page is requested. */
    public static function absolute(string $path): string
    {
        $base = rtrim((string) config('app.url'), '/');
        $path = trim($path);

        if ($path === '' || $path === '/') {
            return $base.'/';
        }

        return $base.'/'.ltrim($path, '/');
    }
}
