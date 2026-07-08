<?php

use App\Domains\Forms\Models\LlcFormation;
use App\Domains\Forms\Models\SalesTaxRegistration;
use App\Support\Workspaces\FormationsWorkspaceData;
use App\Support\Workspaces\LienWorkspaceData;
use App\Support\Workspaces\ResaleCertWorkspaceData;
use App\Support\Workspaces\SalesTaxWorkspaceData;

/*
|--------------------------------------------------------------------------
| Workspace Registry
|--------------------------------------------------------------------------
|
| Each entry describes a product section of the unified portal shell
| (`<x-layouts.portal>` renders every enabled workspace as a labeled
| sidebar group). The array key is the internal workspace key used by
| `WorkspaceRegistry::find($key)`. The `slug` is the URL slug
| (independent from the array key).
|
| All values must be config-cache-safe scalars or class strings — no
| closures — so `php artisan config:cache` works in production.
|
| `form_types` lists the forms-runner form types this workspace claims.
| When non-empty, the workspace's `start_route_name` and
| `application_route_name` host the form runner UI (state selector +
| MultiStateFormRunner). When empty (e.g. Liens), the workspace owns its
| own non-form routes and models.
|
*/

return [

    'liens' => [
        'name' => 'Liens',
        'slug' => 'liens',
        'description' => 'Track deadlines and file mechanics liens across your projects.',
        'icon' => 'scale',
        'badge' => 'Liens',
        'badge_color' => 'amber',
        'dashboard_route' => 'lien.dashboard',
        'enabled' => true,
        'form_types' => [],
        'start_route_name' => null,
        'application_route_name' => null,
        'start_route_param' => null,
        'data_resolver' => LienWorkspaceData::class,
        'nav' => [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'lien.dashboard',
                'current_pattern' => 'lien.dashboard',
            ],
            [
                'label' => 'Projects',
                'icon' => 'folder',
                'route' => 'lien.projects.index',
                'current_pattern' => 'lien.projects.*',
            ],
            [
                'label' => 'Filings',
                'icon' => 'document-text',
                'route' => 'lien.filings.index',
                'current_pattern' => 'lien.filings.index',
            ],
            [
                'label' => 'Waivers',
                'icon' => 'document-check',
                'route' => 'lien.waivers.index',
                // Explicit list (not lien.waivers.*) so the Contacts sub-namespace
                // highlights Contacts only, not both items.
                'current_pattern' => [
                    'lien.waivers.index',
                    'lien.waivers.list',
                    'lien.waivers.create',
                    'lien.waivers.show',
                    'lien.waivers.subscribe',
                    'lien.waivers.payment-confirmation',
                ],
            ],
            [
                'label' => 'Contacts',
                'icon' => 'user-group',
                'route' => 'lien.waivers.contacts.index',
                'current_pattern' => 'lien.waivers.contacts.*',
            ],
            [
                'label' => 'Deadlines',
                'icon' => 'calendar',
                'route' => 'lien.deadlines.index',
                'current_pattern' => 'lien.deadlines.*',
            ],
        ],
    ],

    'sales_tax' => [
        'name' => 'Sales Tax',
        'slug' => 'sales-tax',
        'description' => 'Register for sales and use tax permits across one or more states.',
        'icon' => 'receipt-percent',
        'badge' => 'Sales Tax',
        'badge_color' => 'emerald',
        'dashboard_route' => 'sales-tax.dashboard',
        'enabled' => true,
        // Sourced from the child model so the SalesTaxRegistration global
        // scope, the workspace registry, and the data resolver share one
        // source of truth.
        'form_types' => [SalesTaxRegistration::FORM_TYPE],
        'start_route_name' => 'sales-tax.registrations.start',
        'application_route_name' => 'sales-tax.registrations.show',
        'start_route_param' => null, // form_type baked in via Route::defaults()
        'checkout_route_name' => 'sales-tax.registrations.checkout',
        'confirmation_route_name' => 'sales-tax.registrations.payment-confirmation',
        'data_resolver' => SalesTaxWorkspaceData::class,
        'nav' => [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'sales-tax.dashboard',
                'current_pattern' => 'sales-tax.dashboard',
            ],
        ],
    ],

    'resale_cert' => [
        'name' => 'Resale Certificates',
        'slug' => 'resale-certificates',
        'description' => 'Generate resale certificates for every state you buy in — one subscription, unlimited certificates.',
        'icon' => 'document-check',
        'badge' => 'Resale Certs',
        'badge_color' => 'blue',
        'dashboard_route' => 'resale-cert.dashboard',
        'enabled' => true,
        'form_types' => [],
        'start_route_name' => null,
        'application_route_name' => null,
        'start_route_param' => null,
        'data_resolver' => ResaleCertWorkspaceData::class,
        'nav' => [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'resale-cert.dashboard',
                'current_pattern' => 'resale-cert.dashboard',
            ],
            [
                'label' => 'Certificates',
                'icon' => 'document-check',
                'route' => 'resale-cert.certificates.index',
                'current_pattern' => 'resale-cert.certificates.*',
            ],
            [
                'label' => 'Vendors',
                'icon' => 'building-storefront',
                'route' => 'resale-cert.vendors.index',
                'current_pattern' => 'resale-cert.vendors.*',
            ],
            [
                'label' => 'Settings',
                'icon' => 'cog-6-tooth',
                'route' => 'resale-cert.settings',
                'current_pattern' => 'resale-cert.settings',
            ],
        ],
    ],

    'formations' => [
        'name' => 'Formations',
        'slug' => 'formations',
        'description' => 'Form your business and manage your formation documents.',
        'icon' => 'building-office-2',
        'badge' => 'Formations',
        'badge_color' => 'indigo',
        'dashboard_route' => 'formations.dashboard',
        'enabled' => true,
        // Add more form types here (corporation, dba, nonprofit, sole_proprietorship)
        // and the start route's whereIn() constraint picks them up automatically.
        // Sourced from the child models so each model's global scope and the
        // workspace registry stay aligned without string drift.
        'form_types' => [LlcFormation::FORM_TYPE],
        'start_route_name' => 'formations.start',
        'application_route_name' => 'formations.show',
        'start_route_param' => 'formType', // {formType} URL segment, hydrates StateSelector::mount(string $formType)
        'checkout_route_name' => 'formations.checkout',
        'confirmation_route_name' => 'formations.payment-confirmation',
        'data_resolver' => FormationsWorkspaceData::class,
        'nav' => [
            [
                'label' => 'Dashboard',
                'icon' => 'home',
                'route' => 'formations.dashboard',
                'current_pattern' => 'formations.dashboard',
            ],
        ],
    ],

];
