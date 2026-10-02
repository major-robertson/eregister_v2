<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Seo\BlankDemandLetter;
use App\Domains\Lien\Seo\BlankLienDocument;
use App\Domains\Lien\Seo\LienReleaseStatePage;
use App\Domains\Lien\Seo\LienVariantStatePage;
use App\Domains\Lien\Seo\NoticeOfIntentStatePage;
use App\Support\Seo\States;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Public per-state pages for the notice of intent to lien
 * ("/liens/notice-of-intent-to-lien/texas") and the lien release
 * ("/liens/lien-release/texas"), their free blank PDFs, and the free blank
 * payment demand letter. URLs use the full-name slug; two-letter codes 301
 * to it. The PDFs are indexable but kept out of the sitemap.
 */
class LienVariantLandingController extends Controller
{
    public function noticeOfIntent(string $state): View|RedirectResponse
    {
        return $this->show($state, NoticeOfIntentStatePage::class, 'pages.liens.notice-of-intent-state');
    }

    public function lienRelease(string $state): View|RedirectResponse
    {
        return $this->show($state, LienReleaseStatePage::class, 'pages.liens.lien-release-state');
    }

    public function noticeOfIntentBlank(string $state, BlankLienDocument $blanks): Response|RedirectResponse
    {
        return $this->blank($state, NoticeOfIntentStatePage::class, $blanks);
    }

    public function lienReleaseBlank(string $state, BlankLienDocument $blanks): Response|RedirectResponse
    {
        return $this->blank($state, LienReleaseStatePage::class, $blanks);
    }

    /** Ungated blank payment demand letter ("/liens/payment-demand-letter/blank.pdf"). */
    public function demandLetterBlank(BlankDemandLetter $letter): Response
    {
        return $this->pdf($letter->pdf(), BlankDemandLetter::FILENAME);
    }

    /**
     * @param  class-string<LienVariantStatePage>  $model
     */
    private function show(string $state, string $model, string $view): View|RedirectResponse
    {
        $code = $this->resolve($state, $model);
        $slug = States::slug((string) States::name($code));

        if ($state !== $slug) {
            return redirect()->route($model::ROUTE, ['state' => $slug], 301);
        }

        $page = Cache::remember($model::cacheKey($code), now()->addDay(), fn () => $model::forCode($code));
        abort_unless($page, 404);

        return view($view, ['page' => $page]);
    }

    /**
     * The state's blank document of the page's kind. States without one 404;
     * codes and mixed case 301 to the slug.
     *
     * @param  class-string<LienVariantStatePage>  $model
     */
    private function blank(string $state, string $model, BlankLienDocument $blanks): Response|RedirectResponse
    {
        $code = $this->resolve($state, $model);
        $slug = States::slug((string) States::name($code));

        if ($state !== $slug) {
            return redirect()->route($model::ROUTE.'.blank', ['state' => $slug], 301);
        }

        $form = $blanks->form($code, $model::KIND);
        abort_if($form === null, 404);

        return $this->pdf((string) $blanks->pdf($code, $model::KIND), $blanks->filename($form));
    }

    /**
     * @param  class-string<LienVariantStatePage>  $model
     */
    private function resolve(string $state, string $model): string
    {
        $code = States::codeFromSlug($state);
        abort_unless($code !== null && $model::has($code), 404);

        return $code;
    }

    private function pdf(string $bytes, string $filename): Response
    {
        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
