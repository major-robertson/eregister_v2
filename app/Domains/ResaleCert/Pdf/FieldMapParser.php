<?php

namespace App\Domains\ResaleCert\Pdf;

/**
 * Extracts the coordinate map from a state certificate class's source code
 * so the admin PDF mapper can overlay existing field positions onto the
 * template. Parses the writeAt()/addSignatureWithHeight() call sites — the
 * coordinate literals ARE the source of truth, so reading them beats
 * maintaining a parallel registry.
 */
class FieldMapParser
{
    /**
     * The certificate class whose coordinates target the given template
     * filename (e.g. 'texas.pdf' → TexasCertificate), or null when the
     * template isn't a resale state form.
     *
     * @return class-string|null
     */
    public function classForTemplate(string $filename): ?string
    {
        foreach (config('resale_cert.states', []) as $config) {
            if (($config['template'] ?? null) === $filename || ($config['template_out_of_state'] ?? null) === $filename) {
                return $config['class'];
            }
        }

        return null;
    }

    /**
     * Field markers parsed from the class source: name, x/y in mm (FPDI
     * units), signature height when applicable, and the page they render on.
     *
     * @param  class-string  $className
     * @return list<array{name: string, x: float, y: float, height: float, page: int}>
     */
    public function fieldsFor(string $className): array
    {
        if (! class_exists($className)) {
            return [];
        }

        $source = file_get_contents((new \ReflectionClass($className))->getFileName());

        $fields = [];
        $currentPage = 1;

        foreach (explode("\n", $source) as $line) {
            // Track page context: if ($currentPage === N)
            if (preg_match('/if\s*\(\s*\$currentPage\s*===\s*(\d+)\s*\)/', $line, $pageMatch)) {
                $currentPage = (int) $pageMatch[1];

                continue;
            }

            // $this->writeAt($pdf, X, Y, $data->fieldName);
            if (preg_match('/\$this->writeAt\(\$pdf,\s*([\d.]+),\s*([\d.]+),\s*\$data->(\w+)\)/', $line, $match)) {
                $fields[] = [
                    'name' => $match[3],
                    'x' => (float) $match[1],
                    'y' => (float) $match[2],
                    'height' => 0.0,
                    'page' => $currentPage,
                ];

                continue;
            }

            // $this->addSignatureWithHeight($pdf, $certificate, X, Y, HEIGHT);
            if (preg_match('/\$this->addSignatureWithHeight\(\$pdf,\s*\$certificate,\s*([\d.]+),\s*([\d.]+),\s*([\d.]+)\)/', $line, $match)) {
                $fields[] = [
                    'name' => 'signature',
                    'x' => (float) $match[1],
                    'y' => (float) $match[2],
                    'height' => (float) $match[3],
                    'page' => $currentPage,
                ];

                continue;
            }

            // $this->writeAt($pdf, X, Y, 'LITERAL'); — checkmarks & static text
            if (preg_match('/\$this->writeAt\(\$pdf,\s*([\d.]+),\s*([\d.]+),\s*[\'"]([^\'"]+)[\'"]\)/', $line, $match)) {
                $fields[] = [
                    'name' => $match[3] === 'X' ? 'checkmark' : 'literal: '.$match[3],
                    'x' => (float) $match[1],
                    'y' => (float) $match[2],
                    'height' => 0.0,
                    'page' => $currentPage,
                ];
            }
        }

        return $fields;
    }
}
