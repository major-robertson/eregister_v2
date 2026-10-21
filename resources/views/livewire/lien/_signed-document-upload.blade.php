{{-- The upload box for the signed (or notarized) document, FilingShow::uploadSignedDocument.
     Shown while the filing waits on the customer: alone, or inside the request panel
     ($inRequestPanel) next to the answer form. --}}
<form wire:submit="uploadSignedDocument" @class(['space-y-4', 'border-t border-border pt-4' => $inRequestPanel])>
    @if($inRequestPanel)
        <h3 class="text-base font-semibold text-zinc-900">Upload your signed document</h3>
    @else
        <div class="flex items-center gap-2">
            <flux:icon name="arrow-up-tray" class="w-5 h-5 text-orange-600" />
            <h2 class="text-lg font-semibold text-zinc-900">Upload your signed document</h2>
        </div>
    @endif

    <div class="rounded-lg bg-zinc-50 p-4 text-sm text-zinc-700 space-y-2">
        <p class="font-medium text-zinc-900">Before you upload, check:</p>
        <ul class="list-disc space-y-1 pl-5">
            <li>A PDF is best. A clear photo works too.</li>
            <li>Every signature line is signed.</li>
        </ul>
        <p class="font-medium text-zinc-900">If it was notarized, also check:</p>
        <ul class="list-disc space-y-1 pl-5">
            <li>The notary seal is easy to see.</li>
            <li>The notary's printed name and commission can be read.</li>
            <li>The county is filled in at the top of the notary section.</li>
        </ul>
    </div>

    <flux:field>
        <flux:file-upload wire:model="signedDocument" accept=".pdf,.jpg,.jpeg,.png,.heic,.heif">
            <flux:file-upload.dropzone
                inline
                heading="Drop your document here or click to browse"
                text="PDF, JPG, PNG or HEIC, up to 20 MB"
                with-progress
            />
        </flux:file-upload>
        <flux:error name="signedDocument" />
    </flux:field>

    @if($signedDocument)
        <flux:text class="text-sm">{{ $signedDocument->getClientOriginalName() }}</flux:text>
    @endif

    <flux:button type="submit" variant="primary" icon="arrow-up-tray">
        Upload document
    </flux:button>
</form>
