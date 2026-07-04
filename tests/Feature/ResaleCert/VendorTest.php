<?php

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Livewire\VendorForm;
use App\Domains\ResaleCert\Livewire\VendorList;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Vendor Test Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);
    subscribeToResaleCerts($this->business);
    ResaleProfile::factory()->create(['business_id' => $this->business->id]);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('creates a vendor scoped to the current business', function () {
    Livewire::test(VendorForm::class)
        ->set('legal_name', 'Widget Supply Inc')
        ->set('address_line1', '500 Commerce St')
        ->set('city', 'Fort Worth')
        ->set('state', 'TX')
        ->set('postal_code', '76102')
        ->call('save')
        ->assertHasNoErrors();

    $vendor = ResaleVendor::withoutGlobalScopes()->where('legal_name', 'Widget Supply Inc')->first();

    expect($vendor)->not->toBeNull()
        ->and($vendor->business_id)->toBe($this->business->id)
        ->and($vendor->created_by_user_id)->toBe($this->user->id);
});

it('validates required vendor fields', function () {
    Livewire::test(VendorForm::class)
        ->call('save')
        ->assertHasErrors(['legal_name', 'address_line1', 'city', 'state', 'postal_code']);
});

it('lists only the current business vendors', function () {
    ResaleVendor::factory()->create(['business_id' => $this->business->id, 'legal_name' => 'Mine LLC']);

    $otherBusiness = Business::create(['name' => 'Other Co', 'onboarding_completed_at' => now()]);
    ResaleVendor::factory()->create(['business_id' => $otherBusiness->id, 'legal_name' => 'Theirs LLC']);

    Livewire::test(VendorList::class)
        ->assertSee('Mine LLC')
        ->assertDontSee('Theirs LLC');
});

it('cannot load another business vendor by id', function () {
    $otherBusiness = Business::create(['name' => 'Other Co', 'onboarding_completed_at' => now()]);
    $foreignVendor = ResaleVendor::factory()->create(['business_id' => $otherBusiness->id]);

    $this->get(route('resale-cert.vendors.show', $foreignVendor))->assertNotFound();
});

it('blocks deleting a vendor that has certificates', function () {
    $vendor = ResaleVendor::factory()->create(['business_id' => $this->business->id]);
    ResaleCertificate::factory()->create([
        'business_id' => $this->business->id,
        'resale_vendor_id' => $vendor->id,
    ]);

    Livewire::test(VendorList::class)
        ->call('deleteVendor', $vendor->id);

    expect(ResaleVendor::withoutGlobalScopes()->find($vendor->id))->not->toBeNull();
});

it('deletes a vendor without certificates', function () {
    $vendor = ResaleVendor::factory()->create(['business_id' => $this->business->id]);

    Livewire::test(VendorList::class)
        ->call('deleteVendor', $vendor->id);

    expect($vendor->fresh()->trashed())->toBeTrue();
});

it('updates an existing vendor', function () {
    $vendor = ResaleVendor::factory()->create(['business_id' => $this->business->id]);

    Livewire::test(VendorForm::class, ['vendor' => $vendor])
        ->set('legal_name', 'Renamed Supplier LLC')
        ->call('save')
        ->assertHasNoErrors();

    expect($vendor->fresh()->legal_name)->toBe('Renamed Supplier LLC');
});
