<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /** @return array<int, array{loc: string, changefreq: string, priority: string}> */
    public static function urls(): array
    {
        $pages = [
            ['path' => '/',                              'changefreq' => 'weekly',  'priority' => '1.0'],
            ['path' => '/liens',                         'changefreq' => 'weekly',  'priority' => '0.9'],
            ['path' => '/llc',                           'changefreq' => 'monthly', 'priority' => '0.8'],

            ['path' => '/privacy-policy',                'changefreq' => 'yearly',  'priority' => '0.3'],
            ['path' => '/terms-of-service',              'changefreq' => 'yearly',  'priority' => '0.3'],
            ['path' => '/refund-policy',                 'changefreq' => 'yearly',  'priority' => '0.3'],
            ['path' => '/contact',                       'changefreq' => 'monthly', 'priority' => '0.6'],

            ['path' => '/corporation',                   'changefreq' => 'monthly', 'priority' => '0.8'],
            ['path' => '/dba',                           'changefreq' => 'monthly', 'priority' => '0.8'],
            ['path' => '/nonprofit',                     'changefreq' => 'monthly', 'priority' => '0.8'],
            ['path' => '/sole-proprietorship',           'changefreq' => 'monthly', 'priority' => '0.8'],

            ['path' => '/registered-agent',              'changefreq' => 'monthly', 'priority' => '0.8'],
            ['path' => '/annual-reports',                'changefreq' => 'monthly', 'priority' => '0.8'],
            ['path' => '/ein-tax-id',                    'changefreq' => 'monthly', 'priority' => '0.8'],
            ['path' => '/operating-agreement',           'changefreq' => 'monthly', 'priority' => '0.8'],

            ['path' => '/sales-tax-registration',        'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/resale-certificates',           'changefreq' => 'monthly', 'priority' => '0.7'],

            ['path' => '/liens/preliminary-notice',      'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/liens/notice-of-intent-to-lien', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/liens/lien-release',            'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/liens/payment-demand-letter',   'changefreq' => 'monthly', 'priority' => '0.7'],
        ];

        return array_map(fn (array $page) => [
            'loc' => url($page['path']),
            'changefreq' => $page['changefreq'],
            'priority' => $page['priority'],
        ], $pages);
    }

    public function __invoke(): Response
    {
        $sitemap = Sitemap::create();

        foreach (static::urls() as $entry) {
            $sitemap->add(
                Url::create($entry['loc'])
                    ->setChangeFrequency($entry['changefreq'])
                    ->setPriority((float) $entry['priority'])
            );
        }

        return response($sitemap->render())
            ->header('Content-Type', 'application/xml');
    }
}
