<?php

namespace App\Http\Controllers\Demo\Pcc;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Isolated front-end-only concept demo for the Pitt Community College
 * RFP 115-6181 proposal (website redesign).
 *
 * Content lives in JSON files under resources/demo/pcc that mirror the
 * CMS content types proposed for production (Program, Admissions Pathway,
 * News, Event) plus the Division taxonomy. There is intentionally no
 * database and no live data; destinations outside the demo's eight built
 * pages open a "Demo preview" modal instead of navigating.
 */
class PccDemoController extends Controller
{
    public function home(): View
    {
        return view('demo.pcc.home', [
            'areas' => $this->areas(),
            'news' => $this->data('news'),
            'events' => $this->data('events'),
            'transferProgram' => $this->findProgram('associate-in-arts'),
        ]);
    }

    public function programs(Request $request): View
    {
        $divisions = array_column($this->data('divisions'), 'name');

        return view('demo.pcc.programs', [
            'programs' => $this->allPrograms(),
            'divisions' => $divisions,
            'initialQuery' => (string) $request->query('q', ''),
            'initialArea' => in_array($request->query('area'), $divisions, true)
                ? $request->query('area')
                : 'All',
        ]);
    }

    public function program(string $slug): View
    {
        $program = $this->findProgram($slug);

        abort_if($program === null, 404);

        return view('demo.pcc.program', ['program' => $program]);
    }

    public function admissions(Request $request): View
    {
        $pathways = $this->data('admissions');

        return view('demo.pcc.admissions', [
            'pathways' => $pathways,
            'initialType' => array_key_exists((string) $request->query('type'), $pathways)
                ? $request->query('type')
                : array_key_first($pathways),
        ]);
    }

    public function paying(): View
    {
        return view('demo.pcc.paying');
    }

    public function studentLife(): View
    {
        return view('demo.pcc.student-life', [
            'events' => $this->data('events'),
        ]);
    }

    public function workforce(): View
    {
        return view('demo.pcc.workforce', [
            'shortTermPrograms' => array_values(array_filter(
                $this->allPrograms(),
                fn (array $p): bool => $p['division'] === 'Continuing Education',
            )),
        ]);
    }

    public function about(): View
    {
        return view('demo.pcc.about');
    }

    /**
     * Programs with the shared defaults (cost, deadlines, support, next
     * steps…) merged in, the way the production CMS would resolve them.
     *
     * @return array<int, array<string, mixed>>
     */
    private function allPrograms(): array
    {
        $data = File::json(resource_path('demo/pcc/programs.json'));

        return array_map(
            fn (array $program): array => array_merge($data['defaults'], $program),
            $data['programs'],
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findProgram(string $slug): ?array
    {
        foreach ($this->allPrograms() as $program) {
            if ($program['slug'] === $slug) {
                return $program;
            }
        }

        return null;
    }

    /**
     * Interest areas for the homepage: each division with its live program
     * count, so the tiles never drift from the program list.
     *
     * @return array<int, array<string, mixed>>
     */
    private function areas(): array
    {
        $counts = array_count_values(array_column($this->allPrograms(), 'division'));

        return array_map(fn (array $division): array => [
            ...$division,
            'count' => $counts[$division['name']] ?? 0,
        ], $this->data('divisions'));
    }

    /**
     * @return array<mixed>
     */
    private function data(string $file): array
    {
        return File::json(resource_path("demo/pcc/{$file}.json"));
    }
}
