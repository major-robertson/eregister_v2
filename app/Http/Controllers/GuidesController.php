<?php

namespace App\Http\Controllers;

use App\Support\Seo\Guides;
use Illuminate\View\View;

/**
 * The editorial guides: the hub at "/guides" and one page per entry in the
 * App\Support\Seo\Guides registry at "/guides/{slug}". Each guide's view
 * builds its own body; this controller only resolves the registry entry.
 */
class GuidesController extends Controller
{
    public function index(): View
    {
        return view('pages.guides.index', ['clusters' => Guides::byCluster()]);
    }

    public function show(string $slug): View
    {
        $guide = Guides::find($slug);
        abort_unless($guide, 404);

        return view($guide['view'], ['guide' => $guide]);
    }
}
