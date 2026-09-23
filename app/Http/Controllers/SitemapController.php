<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Domains\ResaleCert\Seo\ResaleStatePage;
use App\Support\Seo\States;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    /**
     * Static marketing pages. `view` names the Blade file whose modification
     * time becomes the entry's <lastmod>; deploys via git only rewrite files
     * that changed, so the timestamp tracks real content edits.
     *
     * @var array<int, array{path: string, changefreq: string, priority: string, view: string}>
     */
    private const PAGES = [
        ['path' => '/',                              'changefreq' => 'weekly',  'priority' => '1.0', 'view' => 'landing'],
        ['path' => '/liens',                         'changefreq' => 'weekly',  'priority' => '0.9', 'view' => 'liens'],
        ['path' => '/llc',                           'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'llc'],

        ['path' => '/privacy-policy',                'changefreq' => 'yearly',  'priority' => '0.3', 'view' => 'pages.privacy-policy'],
        ['path' => '/terms-of-service',              'changefreq' => 'yearly',  'priority' => '0.3', 'view' => 'pages.terms-of-service'],
        ['path' => '/refund-policy',                 'changefreq' => 'yearly',  'priority' => '0.3', 'view' => 'pages.refund-policy'],
        ['path' => '/contact',                       'changefreq' => 'monthly', 'priority' => '0.6', 'view' => 'pages.contact'],

        ['path' => '/corporation',                   'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.corporation'],
        ['path' => '/dba',                           'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.dba'],
        ['path' => '/nonprofit',                     'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.nonprofit'],
        ['path' => '/sole-proprietorship',           'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.sole-proprietorship'],

        ['path' => '/registered-agent',              'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.registered-agent'],
        ['path' => '/annual-reports',                'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.annual-reports'],
        ['path' => '/ein-tax-id',                    'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.ein-tax-id'],
        ['path' => '/operating-agreement',           'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.operating-agreement'],

        ['path' => '/sales-tax-registration',        'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.sales-tax-registration'],
        ['path' => '/resale-certificates',           'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.resale-certificates'],

        ['path' => '/liens/preliminary-notice',      'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.liens.preliminary-notice'],
        ['path' => '/liens/notice-of-intent-to-lien', 'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.liens.notice-of-intent-to-lien'],
        ['path' => '/liens/lien-release',            'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.liens.lien-release'],
        ['path' => '/liens/payment-demand-letter',   'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.liens.payment-demand-letter'],
        ['path' => '/liens/pricing',                 'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.liens.pricing'],

        ['path' => '/liens/lien-waivers',            'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.liens.lien-waivers'],
        ['path' => '/liens/lien-waivers/pricing',    'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.liens.lien-waivers-pricing'],

        ['path' => '/government',                          'changefreq' => 'monthly', 'priority' => '0.8', 'view' => 'pages.government.index'],
        ['path' => '/government/website-redesign',         'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.government.website-redesign'],
        ['path' => '/government/accessibility',            'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.government.accessibility'],
        ['path' => '/government/cms',                      'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.government.cms'],
        ['path' => '/government/hosting',                  'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.government.hosting'],
        ['path' => '/government/maintenance',              'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.government.maintenance'],
        ['path' => '/government/portals',                  'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.government.portals'],
        ['path' => '/government/integrations',             'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.government.integrations'],
        ['path' => '/government/implementation',           'changefreq' => 'monthly', 'priority' => '0.7', 'view' => 'pages.government.implementation'],
    ];

    /** @return array<int, array{loc: string, changefreq: string, priority: string, lastmod: string}> */
    public static function urls(): array
    {
        $entries = [];

        foreach (self::PAGES as $page) {
            $entries[] = self::entry($page['path'], $page['changefreq'], $page['priority'], self::viewModified($page['view']));
        }

        // Lien waiver state pages: content comes from the waiver state data
        // files plus the shared template, so the newest of those wins.
        $waiverModified = max(
            self::viewModified('pages.liens.lien-waivers-state'),
            self::directoryModified(database_path('data/waiver_states')),
        );
        foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
            $entries[] = self::entry('/liens/lien-waivers/'.strtolower($code), 'monthly', '0.6', $waiverModified);
        }

        // Mechanics lien rules by state: driven by the lien rule seed data.
        $lienModified = max(
            self::viewModified('pages.liens.lien-state'),
            self::fileModified(database_path('seeders/data/lien_state_rules.json')),
            self::fileModified(database_path('seeders/data/lien_deadline_rules.json')),
        );
        foreach (States::names() as $name) {
            $entries[] = self::entry('/liens/'.States::slug($name), 'monthly', '0.7', $lienModified);
        }

        // Resale certificate rules by state (states with a sales tax only).
        $resaleModified = max(
            self::viewModified('pages.resale-state'),
            self::fileModified(database_path('seeders/ResaleStateRuleSeeder.php')),
        );
        foreach (ResaleStatePage::availableStates() as $name) {
            $entries[] = self::entry('/resale-certificates/'.States::slug($name), 'monthly', '0.6', $resaleModified);
        }

        return $entries;
    }

    public function __invoke(): Response
    {
        $sitemap = Sitemap::create();

        foreach (static::urls() as $entry) {
            $sitemap->add(
                Url::create($entry['loc'])
                    ->setLastModificationDate(Carbon::parse($entry['lastmod']))
                    ->setChangeFrequency($entry['changefreq'])
                    ->setPriority((float) $entry['priority'])
            );
        }

        return response($sitemap->render())
            ->header('Content-Type', 'application/xml');
    }

    /** @return array{loc: string, changefreq: string, priority: string, lastmod: string} */
    private static function entry(string $path, string $changefreq, string $priority, int $modified): array
    {
        return [
            'loc' => url($path),
            'changefreq' => $changefreq,
            'priority' => $priority,
            'lastmod' => Carbon::createFromTimestamp($modified)->toDateString(),
        ];
    }

    private static function viewModified(string $view): int
    {
        return self::fileModified(resource_path('views/'.str_replace('.', '/', $view).'.blade.php'));
    }

    private static function fileModified(string $path): int
    {
        return is_file($path) ? (int) filemtime($path) : time();
    }

    private static function directoryModified(string $path): int
    {
        $files = is_dir($path) ? glob($path.'/*') : [];

        return $files ? max(array_map('filemtime', $files)) : 0;
    }
}
