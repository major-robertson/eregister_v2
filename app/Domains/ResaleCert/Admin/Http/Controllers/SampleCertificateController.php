<?php

namespace App\Domains\ResaleCert\Admin\Http\Controllers;

use App\Domains\ResaleCert\Pdf\SampleCertificate;
use App\Domains\ResaleCert\Pdf\StateCertificateFactory;
use App\Domains\ResaleCert\Services\CertificatePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use setasign\Fpdi\Fpdi;

/**
 * Admin coordinate-mapper previews: renders a certificate for any state
 * from fixed sample data (nothing is saved) so field positions can be
 * checked against the real pipeline — optionally with the mm grid overlay,
 * an out-of-state tax id (exercises GA/NJ alternate templates and per-state
 * branching), and an all-positions overlay for the MTC/SST uniform forms.
 */
class SampleCertificateController
{
    public function __invoke(Request $request, string $stateCode, CertificatePdfService $pdfService, StateCertificateFactory $factory): Response
    {
        $stateCode = strtoupper($stateCode);

        abort_unless($factory->has($stateCode), 404, 'No certificate form for this state.');

        $showGrid = $request->boolean('grid', true);

        if (in_array($stateCode, ['MTC', 'SST'], true) && $request->boolean('all_tax_ids')) {
            return $this->uniformWithAllTaxIds($stateCode);
        }

        $outOfState = $request->query('out_of_state');
        $sourceState = $outOfState ? strtoupper($outOfState === '1' || $outOfState === 'true' ? 'CT' : $outOfState) : $stateCode;

        $certificate = SampleCertificate::make($stateCode, $sourceState);
        // Unsaved model: hydrate the relation so the admin's own adopted
        // signature stamps onto the preview (position checking).
        $certificate->setRelation('createdBy', $request->user());

        $bytes = $pdfService->renderCertificate($certificate, $showGrid);

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sample-'.strtolower($stateCode).($showGrid ? '-grid' : '').'.pdf"',
        ]);
    }

    /**
     * MTC/SST forms place a tax id per member state — overlay a sample value
     * at EVERY mapped position at once so the whole coordinate table can be
     * eyeballed against the form in one render.
     */
    protected function uniformWithAllTaxIds(string $uniformType): Response
    {
        $className = config("resale_cert.states.{$uniformType}.class");
        $template = config("resale_cert.states.{$uniformType}.template");
        $templatePath = resource_path(config('resale_cert.templates_path').'/'.$template);

        abort_unless($className && file_exists($templatePath), 404);

        $reflection = new \ReflectionProperty($className, 'taxIdCoordinates');
        $coordinates = $reflection->getValue(new $className);

        $pdf = new Fpdi;
        $pdf->setSourceFile($templatePath);
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplIdx);
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);

        $sampleTaxId = '123456789';

        foreach ($coordinates as $state => $coords) {
            $pdf->SetXY($coords['x'], $coords['y']);

            if ($uniformType === 'MTC') {
                $pdf->Write(0, $state.' '.$sampleTaxId);
            } else {
                $pdf->Write(0, $sampleTaxId);
                $pdf->SetXY($coords['x'] + 67, $coords['y']);
                $pdf->Write(0, $state);
                $pdf->SetXY($coords['x'] + 82, $coords['y']);
                $pdf->Write(0, 'G');
            }
        }

        return response($pdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="sample-'.strtolower($uniformType).'-all-tax-ids.pdf"',
        ]);
    }
}
