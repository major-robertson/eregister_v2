@extends('layouts.landing')

{{-- Renders a Livewire component: load Livewire and Flux instead of marketing.js. --}}
@section('livewire', true)

@section('title', $businessName . ' - Lien Services')

@section('canonical', $canonicalUrl)
@section('noindex', 'true')

@section('content')
<livewire:marketing.contractor-landing :tracking-link-id="$trackingLinkId" :source="$source" />
@endsection