<?php

namespace Database\Seeders;

use App\Domains\Marketing\Models\MarketingRedirect;
use Illuminate\Database\Seeder;

class MarketingRedirectSeeder extends Seeder
{
    /**
     * @var array<int, array{slug: string, destination_path: string, utm_source: string, utm_medium: string, utm_campaign: string}>
     */
    protected array $redirects = [
        [
            'slug' => 'plumbingforums',
            'destination_path' => '/liens',
            'utm_source' => 'plumbingforums',
            'utm_medium' => 'banner',
            'utm_campaign' => 'plumbingforums-banner',
        ],
    ];

    public function run(): void
    {
        foreach ($this->redirects as $redirect) {
            MarketingRedirect::firstOrCreate(
                ['slug' => $redirect['slug']],
                $redirect,
            );
        }
    }
}
