<?php

use App\Domains\Admin\Livewire\PdfCoordinateMapper;
use App\Domains\ResaleCert\Pdf\FieldMapParser;
use App\Domains\ResaleCert\Pdf\States\TexasCertificate;
use App\Models\User;

describe('access control', function () {
    it('allows admins in and shows the sidebar link', function () {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.tools.pdf-mapper'))
            ->assertSuccessful()
            ->assertSee('PDF Coordinate Mapper')
            ->assertSee('PDF Mapper')
            ->assertSee('Resale certificate templates');
    });

    it('denies non-admin users', function () {
        $user = User::factory()->create();
        $user->assignRole('lien_agent');

        $this->actingAs($user)
            ->get(route('admin.tools.pdf-mapper'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.tools.pdf-mapper.sample', 'TX'))
            ->assertForbidden();
    });

    it('redirects guests to login', function () {
        $this->get(route('admin.tools.pdf-mapper'))->assertRedirect(route('login'));
    });
});

describe('template payloads', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);
    });

    it('serves a library template with its parsed field map', function () {
        $component = new PdfCoordinateMapper;
        $component->template = 'texas.pdf';
        $payload = $component->pdfPayload();

        expect(str_starts_with(base64_decode($payload['base64']), '%PDF'))->toBeTrue()
            ->and($payload['name'])->toBe('texas.pdf');

        $fieldNames = array_column($payload['fields'], 'name');

        expect($fieldNames)->toContain('businessName')
            ->toContain('signature');
    });

    it('rejects template names outside the library listing', function () {
        $component = new PdfCoordinateMapper;
        $component->template = '../../.env';

        expect(fn () => $component->pdfPayload())
            ->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class);
    });
});

describe('field map parser', function () {
    it('resolves template filenames to certificate classes', function () {
        $parser = new FieldMapParser;

        expect($parser->classForTemplate('texas.pdf'))->toBe(TexasCertificate::class)
            ->and($parser->classForTemplate('georgia_out_of_state.pdf'))->not->toBeNull()
            ->and($parser->classForTemplate('not-a-template.pdf'))->toBeNull();
    });

    it('parses coordinates with page context', function () {
        $fields = (new FieldMapParser)->fieldsFor(TexasCertificate::class);

        $businessName = collect($fields)->firstWhere('name', 'businessName');

        expect($businessName)->not->toBeNull()
            ->and($businessName['x'])->toBe(11.8)
            ->and($businessName['page'])->toBe(1);

        $signature = collect($fields)->firstWhere('name', 'signature');

        expect($signature['height'])->toBeGreaterThan(0);
    });
});

describe('sample renders', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin);
    });

    it('streams a gridded sample certificate through the real pipeline', function () {
        $response = $this->get(route('admin.tools.pdf-mapper.sample', 'TX').'?grid=1');

        $response->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf');

        expect(str_starts_with($response->streamedContent ?? $response->getContent(), '%PDF'))->toBeTrue();
    });

    it('renders the out-of-state variant', function () {
        $this->get(route('admin.tools.pdf-mapper.sample', 'GA').'?out_of_state=1')
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf');
    });

    it('renders the MTC all-tax-id overlay', function () {
        $this->get(route('admin.tools.pdf-mapper.sample', 'MTC').'?all_tax_ids=1')
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf');
    });

    it('404s for unknown states', function () {
        $this->get(route('admin.tools.pdf-mapper.sample', 'ZZ'))
            ->assertNotFound();
    });
});
