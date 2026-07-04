<?php

namespace App\Domains\Admin\Livewire;

use App\Domains\ResaleCert\Pdf\FieldMapParser;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Generic PDF coordinate mapper for building FPDI field maps: renders any
 * PDF (uploaded, or from a configured template library) in the browser with
 * a live mm cursor readout, click-to-pin markers that emit ready-to-paste
 * writeAt() snippets, and an optional mm grid — the same units the FPDI
 * generators use. Resale certificate templates additionally overlay the
 * EXISTING field map parsed from their state class and offer sample
 * renders through the real generation pipeline. Ported from the old
 * TaxResaleCertificate dev tools; built generic so lien/government PDF
 * forms can use it next.
 */
class PdfCoordinateMapper extends Component
{
    use WithFileUploads;

    /** 'library' or 'upload' */
    public string $source = 'library';

    public string $library = 'resale_certificates';

    /** Template filename within the library (e.g. 'texas.pdf'). */
    public string $template = '';

    public $upload = null;

    public function updatedUpload(): void
    {
        $this->validate([
            'upload' => ['file', 'mimes:pdf', 'max:20480'],
        ]);

        $this->source = 'upload';
        $this->dispatch('pdf-mapper-load');
    }

    public function updatedTemplate(): void
    {
        $this->source = 'library';
        $this->dispatch('pdf-mapper-load');
    }

    /**
     * @return array<string, array{label: string, files: list<string>}>
     */
    #[Computed]
    public function libraries(): array
    {
        $libraries = [];

        foreach (config('pdf_mapper.libraries', []) as $key => $config) {
            $libraries[$key] = ['label' => $config['label'], 'files' => $this->libraryFiles($key)];
        }

        return $libraries;
    }

    /**
     * @return list<string>
     */
    protected function libraryFiles(string $key): array
    {
        $config = config("pdf_mapper.libraries.{$key}");

        if (! $config || ! is_dir($dir = resource_path($config['path']))) {
            return [];
        }

        $files = [];

        foreach (scandir($dir) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
                $files[] = $file;
            }
        }

        sort($files);

        return $files;
    }

    /**
     * The selected PDF as base64 for the pdf.js viewer. Called on demand
     * from the client so multi-MB templates never sit in the component
     * snapshot.
     */
    public function pdfPayload(): array
    {
        if ($this->source === 'upload' && $this->upload) {
            return [
                'base64' => base64_encode(file_get_contents($this->upload->getRealPath())),
                'name' => $this->upload->getClientOriginalName(),
                'fields' => [],
            ];
        }

        $config = config("pdf_mapper.libraries.{$this->library}");

        abort_unless($config && $this->template !== '', 404);

        // The template list comes from scandir, but re-validate against it —
        // never trust a filename round-tripped through the client.
        abort_unless(in_array($this->template, $this->libraryFiles($this->library), true), 404);

        $path = resource_path($config['path'].'/'.$this->template);

        return [
            'base64' => base64_encode(file_get_contents($path)),
            'name' => $this->template,
            'fields' => $this->existingFields(),
        ];
    }

    /**
     * Existing coordinate map for resale templates (empty for other
     * sources) so current positions render as labeled markers.
     *
     * @return list<array{name: string, x: float, y: float, height: float, page: int}>
     */
    protected function existingFields(): array
    {
        if ($this->library !== 'resale_certificates') {
            return [];
        }

        $parser = app(FieldMapParser::class);
        $class = $parser->classForTemplate($this->template);

        return $class ? $parser->fieldsFor($class) : [];
    }

    /**
     * Resale states offered for sample renders through the real pipeline.
     *
     * @return array<string, string>
     */
    #[Computed]
    public function sampleStates(): array
    {
        return collect(config('resale_cert.states', []))
            ->map(fn ($config) => $config['name'])
            ->sort()
            ->all();
    }

    public function render(): View
    {
        return view('admin.pdf-coordinate-mapper')
            ->layout('layouts.admin', ['title' => 'PDF Coordinate Mapper']);
    }
}
