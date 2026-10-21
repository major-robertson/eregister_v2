<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Contracts\SignatureContext;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Documents\PdfSignatureStamper;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

/*
| EREG-141: PdfSignatureStamper carries every page of an uploaded PDF over
| as it is and appends the signature block and Certificate of Completion.
*/

beforeEach(function () {
    Storage::fake('s3');
});

if (! function_exists('stamperFixturePdf')) {
    /** A PDF made by DOMPDF, legal size so its pages differ from the appended letter page. */
    function stamperFixturePdf(int $pages = 2, string $paper = 'legal'): string
    {
        $body = collect(range(1, $pages))
            ->map(fn (int $n) => '<div'.($n > 1 ? ' style="page-break-before: always"' : '').">Draft page {$n}</div>")
            ->implode('');

        $dompdf = new Dompdf;
        $dompdf->loadHtml("<html><body>{$body}</body></html>");
        $dompdf->setPaper($paper);
        $dompdf->render();

        return $dompdf->output();
    }
}

if (! function_exists('stamperPageSizes')) {
    /** @return list<array{0: float, 1: float}> width and height of each page in mm */
    function stamperPageSizes(string $bytes): array
    {
        $pdf = new Fpdi;
        $count = $pdf->setSourceFile(StreamReader::createByString($bytes));

        return collect(range(1, $count))->map(function (int $n) use ($pdf) {
            $size = $pdf->getTemplateSize($pdf->importPage($n));

            return [round($size['width'], 1), round($size['height'], 1)];
        })->all();
    }
}

if (! function_exists('stamperHandmadePdf')) {
    /**
     * A tiny PDF with a classic cross-reference table, from object bodies
     * numbered 1..n (object 1 is the catalog).
     *
     * @param  list<string>  $objects
     */
    function stamperHandmadePdf(array $objects): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $body) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$body}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}

if (! function_exists('stamperXrefStreamPdf')) {
    /** A PDF 1.5 file whose cross-reference is a stream (FPDI's free parser can't read it). */
    function stamperXrefStreamPdf(): string
    {
        $pdf = "%PDF-1.5\n";
        $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $pdf .= "2 0 obj\n<< /Type /Pages /Kids [] /Count 0 >>\nendobj\n";
        $xref = strlen($pdf);
        $pdf .= "3 0 obj\n<< /Type /XRef /Size 4 /Root 1 0 R /W [1 2 1] /Length 0 >>\nstream\n\nendstream\nendobj\n";

        return $pdf."startxref\n{$xref}\n%%EOF\n";
    }
}

if (! function_exists('stamperContext')) {
    /** A signing session with an audit trail, as CompleteSignature hands it to renderSigned. */
    function stamperContext(?string $signatureImage = null): SignatureContext
    {
        $business = Business::factory()->create();
        $signer = User::factory()->create(['first_name' => 'Dana', 'last_name' => 'Builder', 'email' => 'dana@example.test', 'email_verified_at' => now()]);
        $project = LienProject::factory()->forBusiness($business)->create();
        $filing = LienFiling::factory()->forProject($project)->paid()->create([
            'document_type_id' => LienDocumentType::where('slug', 'prelim_notice')->firstOrFail()->id,
            'created_by_user_id' => $signer->id,
        ]);

        $request = SignatureRequest::create([
            'signable_type' => $filing->getMorphClass(),
            'signable_id' => $filing->id,
            'business_id' => $business->id,
            'signer_user_id' => $signer->id,
            'document_signing_policy_key' => 'uploaded_document',
            'status' => SignatureRequestStatus::Signing,
            'signer_name_snapshot' => 'Dana Builder',
            'signer_email_snapshot' => 'dana@example.test',
            'intent_statement' => config('esign.document_types.uploaded_document.intent'),
            'adopted_name' => 'Dana Builder',
            'email_verified_at_sign' => now(),
        ]);

        $document = SignatureDocument::create([
            'signature_request_id' => $request->id,
            'document_identifier' => 'LD-1001',
            'label' => 'Preliminary Notice v1',
            'recipient_ref' => $filing->public_id,
            'document_snapshot_json' => ['render' => [], 'meta' => []],
            'locked_document_hash' => str_repeat('a', 64),
            'locked_at' => now(),
        ]);

        $events = app(AppendSignatureEvent::class);
        $events->execute($request, SignatureEventType::DocumentLocked, document: $document, actorType: 'admin',
            ip: '198.51.100.2', metadata: ['document_identifier' => 'LD-1001']);
        $events->execute($request, SignatureEventType::SignatureCompleted, actorType: 'signer', actorUserId: $signer->id,
            ip: '203.0.113.7', metadata: ['adopted_name' => 'Dana Builder']);

        return new SignatureContext(
            adoptedName: 'Dana Builder',
            signedAtUtc: Carbon::parse('2026-10-20 18:30:00', 'UTC'),
            signatureId: 'LD-1001',
            request: $request->fresh(),
            document: $document->fresh(),
            signatureImageDataUri: $signatureImage,
            signatureMethod: 'typed_name',
        );
    }
}

it('carries the source pages over untouched and appends one signature page', function () {
    $source = stamperFixturePdf(2, 'legal');
    $context = stamperContext(tinySignaturePng());
    $stamper = app(PdfSignatureStamper::class);

    $signed = $stamper->stamp($source, $context);

    expect($signed)->toStartWith('%PDF-');

    $sizes = stamperPageSizes($signed);
    expect($sizes)->toHaveCount(3)                       // 2 source pages + 1 signature page
        ->and(array_slice($sizes, 0, 2))->toBe(stamperPageSizes($source))
        ->and($sizes[0])->toBe([215.9, 355.6])          // legal, as uploaded
        ->and($sizes[2])->toBe([215.9, 279.4]);         // the appended letter page

    // The appended page is the DOMPDF render (its DejaVu font is not in the source).
    expect($source)->not->toContain('DejaVuSans')
        ->and($signed)->toContain('DejaVuSans');
});

it('prints the signature, ids, IP and certificate on the appended page', function () {
    $context = stamperContext(tinySignaturePng());
    $stamper = app(PdfSignatureStamper::class);

    $html = view('documents.esign.signature-page', $stamper->signaturePageData($context, 2))->render();

    expect($html)
        ->toContain('Electronically signed by:')
        ->toContain('Dana Builder — electronically signed on Oct 20, 2026 2:30 PM ET')
        ->toContain('Signature ID: LD-1001')
        ->toContain('Document ID: LD-1001')
        ->toContain('Signed from IP address: 203.0.113.7')
        ->toContain('the 2 pages before it')
        ->toContain('Preliminary Notice v1')
        ->toContain('Certificate of Completion')
        ->toContain('dana@example.test')
        ->toContain(config('esign.document_types.uploaded_document.intent'))
        ->toContain('198.51.100.2')
        ->toContain('src="'.tinySignaturePng().'"');

    // The rendered page alone is one letter page.
    expect(stamperPageSizes($stamper->renderSignaturePages($context, 2)))->toBe([[215.9, 279.4]]);
});

it('falls back to the typed name when there is no signature image', function () {
    $context = stamperContext();

    $html = view('documents.esign.signature-page', app(PdfSignatureStamper::class)->signaturePageData($context, 1))->render();

    expect($html)->toContain('<div class="esign-signature-name">Dana Builder</div>')
        ->toContain('the 1 page before it');
});

it('refuses a PDF saved with a cross-reference stream, with a message the admin can act on', function () {
    expect(fn () => app(PdfSignatureStamper::class)->pageCount(stamperXrefStreamPdf()))
        ->toThrow(EsignException::class, "This PDF uses a format we can't sign. Re-save it as PDF 1.4 or print it to PDF");
});

it('refuses a PDF with form fields that would drop off the signed copy', function () {
    $withField = stamperHandmadePdf([
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Annots [4 0 R] >>',
        '<< /Type /Annot /Subtype /Widget /Rect [100 100 200 120] /FT /Tx /T (name) /V (Dana) >>',
    ]);

    expect(fn () => app(PdfSignatureStamper::class)->pageCount($withField))
        ->toThrow(EsignException::class, 'form fields or comments');

    // A link annotation is fine.
    $withLink = stamperHandmadePdf([
        '<< /Type /Catalog /Pages 2 0 R >>',
        '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
        '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Annots [4 0 R] >>',
        '<< /Type /Annot /Subtype /Link /Rect [100 100 200 120] /A << /S /URI /URI (https://eregister.com) >> >>',
    ]);

    expect(app(PdfSignatureStamper::class)->pageCount($withLink))->toBe(1);
});

it('refuses bytes that are not a PDF', function () {
    expect(fn () => app(PdfSignatureStamper::class)->pageCount('not a pdf at all'))
        ->toThrow(EsignException::class, "We couldn't read this PDF");
});
