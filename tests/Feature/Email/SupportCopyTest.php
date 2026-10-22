<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\ChangeFilingStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Mail\UploadReceived;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/*
| EREG-155: service mail named in mail.copy_to_support goes out with a Bcc to
| the support inbox, so Front shows the email a customer is answering. Like
| MailGuardTest, no Mail::fake(): the copy is added on MessageSending inside
| the real Mailer, and the array transport captures what was sent.
*/

beforeEach(function () {
    Storage::fake('s3');
    config()->set('mail.support_address', 'contact@eregister.com');

    $this->customer = User::factory()->create(['first_name' => 'Dana', 'email' => 'dana@example.com']);
    $business = Business::factory()->onboarded()->withLienOnboarding()->create();
    $business->users()->attach($this->customer, ['role' => 'owner']);
    $project = LienProject::factory()->forBusiness($business)->create(['created_by_user_id' => $this->customer->id]);

    $this->filing = LienFiling::factory()->forProject($project)->paid()->create([
        'document_type_id' => LienDocumentType::where('slug', 'prelim_notice')->firstOrFail()->id,
        'created_by_user_id' => $this->customer->id,
    ]);
});

/** Bcc addresses of each message the array transport captured. */
function supportCopyBccs(): array
{
    return Mail::getSymfonyTransport()->messages()
        ->map(fn ($sent) => array_map(fn ($address) => $address->getAddress(), $sent->getOriginalMessage()->getBcc()))
        ->all();
}

it('copies the support inbox on a listed service email', function () {
    config()->set('mail.copy_to_support', ['UploadReceived']);

    Mail::to($this->customer)->send(new UploadReceived($this->filing, 'contract.pdf', $this->customer));

    expect(supportCopyBccs())->toBe([['contact@eregister.com']]);
});

it('sends no copy when the email is not listed', function () {
    config()->set('mail.copy_to_support', ['FilingActionRequested']);

    Mail::to($this->customer)->send(new UploadReceived($this->filing, 'contract.pdf', $this->customer));

    expect(supportCopyBccs())->toBe([[]]);
});

it('sends no copy of an email that does not reply to support', function () {
    config()->set('mail.copy_to_support', ['WelcomeEmail']);

    Mail::to($this->customer)->send(new WelcomeEmail($this->customer));

    expect(supportCopyBccs())->toBe([[]]);
});

it('sends nothing at all when the customer is a test account', function () {
    config()->set('mail.copy_to_support', ['UploadReceived']);
    $tester = User::factory()->create(['email' => 'tester@test.test']);

    Mail::to($tester)->send(new UploadReceived($this->filing, 'contract.pdf', $tester));

    expect(supportCopyBccs())->toBe([]);
});

it('copies the request an admin sends with Awaiting Client', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(['lien.view', 'lien.update', 'lien.change_status']);
    $this->actingAs($admin);

    app(ChangeFilingStatus::class)->execute(
        filing: $this->filing,
        newStatus: FilingStatus::AwaitingClient,
        customerMessage: 'Please send the signed contract.',
    );

    $request = Mail::getSymfonyTransport()->messages()
        ->first(fn ($sent) => str_starts_with((string) $sent->getOriginalMessage()->getSubject(), 'We need information'));

    expect($request)->not->toBeNull()
        ->and(array_map(fn ($address) => $address->getAddress(), $request->getOriginalMessage()->getBcc()))
        ->toBe(['contact@eregister.com']);
});
