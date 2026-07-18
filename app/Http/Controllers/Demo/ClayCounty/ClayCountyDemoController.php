<?php

namespace App\Http\Controllers\Demo\ClayCounty;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;

/**
 * Isolated front-end-only concept demo for the Clay County, Missouri
 * RFP 78-26 proposal (Parks, Recreation & Historic Sites website).
 *
 * Content lives in JSON files under resources/demo/clay-county that mirror
 * the WordPress custom post types proposed for production (Destination,
 * Trail, Event, Alert, FAQ). There is intentionally no database, no forms
 * that submit, and no live data — simulated conditions are labeled
 * "Prototype data" in the views.
 */
class ClayCountyDemoController extends Controller
{
    public function home(): View
    {
        return $this->page('home', [
            'destinations' => $this->data('destinations'),
            'events' => $this->data('events'),
        ]);
    }

    public function explore(): View
    {
        return $this->page('explore', [
            'destinations' => $this->data('destinations'),
        ]);
    }

    public function smithvilleLake(): View
    {
        return $this->page('smithville-lake', [
            'destinations' => $this->data('destinations'),
            'trails' => $this->data('trails'),
            'faqs' => $this->data('faqs'),
            'events' => $this->data('events'),
        ]);
    }

    public function trails(): View
    {
        return $this->page('trails', [
            'trails' => $this->data('trails'),
        ]);
    }

    public function historicSites(): View
    {
        return $this->page('historic-sites', [
            'destinations' => $this->data('destinations'),
            'events' => $this->data('events'),
        ]);
    }

    public function jesseJamesBirthplace(): View
    {
        return $this->page('jesse-james-birthplace', [
            'destinations' => $this->data('destinations'),
            'events' => $this->data('events'),
        ]);
    }

    public function events(): View
    {
        return $this->page('events', [
            'events' => $this->data('events'),
            'destinations' => $this->data('destinations'),
        ]);
    }

    public function planYourVisit(): View
    {
        return $this->page('plan-your-visit', [
            'faqs' => $this->data('faqs'),
        ]);
    }

    /**
     * Every page shares the alert feed (banner + drawer) and the search
     * index used by the global search overlay.
     *
     * @param  array<string, mixed>  $data
     */
    private function page(string $view, array $data = []): View
    {
        return view("demo.clay.{$view}", $data + [
            'alerts' => $this->data('alerts'),
            'searchIndex' => $this->searchIndex(),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function data(string $file): array
    {
        return File::json(resource_path("demo/clay-county/{$file}.json"));
    }

    /**
     * Flat index of destinations, trails, events, and FAQs for the
     * client-side search overlay.
     *
     * @return array<int, array<string, string>>
     */
    private function searchIndex(): array
    {
        $index = [];

        foreach ($this->data('destinations') as $destination) {
            $index[] = [
                'type' => 'Destination',
                'title' => $destination['name'],
                'meta' => $destination['typeLabel'],
                'keywords' => $destination['name'].' '.$destination['summary'].' '.implode(' ', $destination['activities']),
                'url' => $destination['url'],
            ];
        }

        foreach ($this->data('trails') as $trail) {
            $index[] = [
                'type' => 'Trail',
                'title' => $trail['name'],
                'meta' => $trail['distance'].' · '.$trail['activityLabel'],
                'keywords' => $trail['name'].' '.$trail['description'].' trail hike '.$trail['activityLabel'],
                'url' => route('clay-demo.trails').'#trail-'.$trail['slug'],
            ];
        }

        foreach ($this->data('events') as $event) {
            $index[] = [
                'type' => 'Event',
                'title' => $event['title'],
                'meta' => $event['dateLabel'].' · '.$event['location'],
                'keywords' => $event['title'].' '.$event['description'].' event',
                'url' => route('clay-demo.events').'#event-'.$event['slug'],
            ];
        }

        foreach ($this->data('faqs') as $faq) {
            $index[] = [
                'type' => 'FAQ',
                'title' => $faq['question'],
                'meta' => 'Plan your visit',
                'keywords' => $faq['question'].' '.$faq['answer'],
                'url' => route('clay-demo.plan-your-visit').'#faq',
            ];
        }

        return $index;
    }
}
