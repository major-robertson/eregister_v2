<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Esign\Contracts\SignatureContext;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Support\SignatureCertificateData;
use Closure;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\FpdiException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParser;
use setasign\Fpdi\PdfParser\StreamReader;
use setasign\Fpdi\PdfParser\Type\PdfArray;
use setasign\Fpdi\PdfParser\Type\PdfDictionary;
use setasign\Fpdi\PdfParser\Type\PdfName;
use setasign\Fpdi\PdfParser\Type\PdfType;
use setasign\Fpdi\PdfReader\PdfReader;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Signs an uploaded lien document (EREG-141): every page of the stored PDF is
 * carried over as it is (FPDI imports each page at its own size), then a page
 * is appended with the electronic signature block and the Certificate of
 * Completion. That page is rendered by DOMPDF from the same partials and facts
 * as the signed demand letter (SignatureCertificateData), plus the document
 * id and the IP address the signer signed from. When the audit trail runs
 * long, the certificate continues on a second page.
 *
 * FPDI's free parser can't read PDF 1.5+ cross-reference streams (Word and
 * Acrobat often save that way), encrypted PDFs, or carry over form fields and
 * comments. pageCount() checks all of that up front so the admin sees a
 * clear EsignException when sending, before the customer is asked to sign.
 */
class PdfSignatureStamper
{
    /** Bump when the appended page's layout or wording changes (frozen in each snapshot). */
    public const VERSION = 1;

    public const UNSUPPORTED_FORMAT = "This PDF uses a format we can't sign. Re-save it as PDF 1.4 or print it to PDF, then upload it again.";

    public const ENCRYPTED = "This PDF is password protected, so we can't sign it. Remove the password or print it to PDF, then upload it again.";

    public const HAS_ANNOTATIONS = 'This PDF has form fields or comments that would drop off the signed copy. Flatten it (print it to PDF), then upload it again.';

    public const UNREADABLE = "We couldn't read this PDF. Re-save it as PDF 1.4 or print it to PDF, then upload it again.";

    /**
     * Annotations FPDI may drop without changing what the page shows: links
     * (FPDI carries external links over anyway) and the popups of comments.
     */
    private const HARMLESS_ANNOTATIONS = ['Link', 'Popup'];

    /**
     * The signed PDF: the source pages, then the signature and certificate.
     *
     * @throws EsignException when the source can't be carried over as it is.
     */
    public function stamp(string $source, SignatureContext $context): string
    {
        $sourcePages = $this->pageCount($source);
        $appendix = $this->renderSignaturePages($context, $sourcePages);

        return $this->guard(function () use ($source, $appendix, $context): string {
            $pdf = new Fpdi;
            $pdf->SetAutoPageBreak(false);
            $pdf->SetCreator('eRegister');
            $pdf->SetTitle($context->document->label, true);

            $this->appendPages($pdf, $source);
            $this->appendPages($pdf, $appendix);

            return $pdf->Output('S');
        });
    }

    /**
     * The source's page count, after checking every page can be carried over
     * as it is.
     *
     * @throws EsignException
     */
    public function pageCount(string $source): int
    {
        return $this->guard(function () use ($source): int {
            $reader = new PdfReader(new PdfParser(StreamReader::createByString($source)));
            $count = $reader->getPageCount();

            if ($count < 1) {
                throw new EsignException(self::UNREADABLE);
            }

            for ($number = 1; $number <= $count; $number++) {
                $this->assertNothingDropsOff($reader, $number);
            }

            return $count;
        });
    }

    /**
     * The appended signature and certificate page(s), rendered alone.
     */
    public function renderSignaturePages(SignatureContext $context, int $sourcePages): string
    {
        return Pdf::view('documents.esign.signature-page', $this->signaturePageData($context, $sourcePages))
            ->driver('dompdf')
            ->format('letter')
            ->generatePdfContent();
    }

    /**
     * @return array{signature: array<string, mixed>, certificate: array<string, mixed>, document: array{identifier: ?string, label: string, pages: int, ip: ?string}}
     */
    public function signaturePageData(SignatureContext $context, int $sourcePages): array
    {
        // The address the signer clicked Sign from, as the certificate's
        // audit trail prints it for the Signature completed event.
        $signedFrom = $context->request->events()
            ->where('event_type', SignatureEventType::SignatureCompleted->value)
            ->reorder('id', 'desc')
            ->value('ip_address');

        return [
            'signature' => SignatureCertificateData::signatureBlock($context),
            'certificate' => SignatureCertificateData::certificate($context),
            'document' => [
                'identifier' => $context->document->document_identifier,
                'label' => $context->document->label,
                'pages' => $sourcePages,
                'ip' => $signedFrom,
            ],
        ];
    }

    private function appendPages(Fpdi $pdf, string $bytes): void
    {
        $count = $pdf->setSourceFile(StreamReader::createByString($bytes));

        for ($number = 1; $number <= $count; $number++) {
            $template = $pdf->importPage($number);
            $size = $pdf->getTemplateSize($template);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($template);
        }
    }

    /**
     * Refuse a page with form fields, comments or markups: FPDI imports the
     * page content only, so they would show on screen but not on the signed
     * copy.
     */
    private function assertNothingDropsOff(PdfReader $reader, int $number): void
    {
        $page = $reader->getPage($number);
        $parser = $reader->getParser();
        $annotations = PdfType::resolve(PdfDictionary::get($page->getPageDictionary(), 'Annots'), $parser);

        if (! $annotations instanceof PdfArray) {
            return;
        }

        foreach ($annotations->value as $entry) {
            $annotation = PdfType::resolve($entry, $parser);
            $subtype = $annotation instanceof PdfDictionary
                ? PdfType::resolve(PdfDictionary::get($annotation, 'Subtype'), $parser)
                : null;

            if (! $subtype instanceof PdfName || ! in_array($subtype->value, self::HARMLESS_ANNOTATIONS, true)) {
                throw new EsignException(self::HAS_ANNOTATIONS);
            }
        }
    }

    /**
     * Run an FPDI step, turning its parser errors into a message the admin
     * can act on.
     *
     * @template T
     *
     * @param  Closure(): T  $step
     * @return T
     *
     * @throws EsignException
     */
    private function guard(Closure $step): mixed
    {
        try {
            return $step();
        } catch (CrossReferenceException $e) {
            throw new EsignException(match ($e->getCode()) {
                CrossReferenceException::COMPRESSED_XREF => self::UNSUPPORTED_FORMAT,
                CrossReferenceException::ENCRYPTED => self::ENCRYPTED,
                default => self::UNREADABLE,
            }, previous: $e);
        } catch (FpdiException $e) {
            throw new EsignException(self::UNREADABLE, previous: $e);
        }
    }
}
