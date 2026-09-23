@extends('layouts.landing')

@section('title', $businessName . ' - Lien Services')

@section('canonical', $canonicalUrl)
@section('noindex', 'true')

@section('content')
<livewire:marketing.contractor-landing :tracking-link-id="$trackingLinkId" :source="$source" />
@endsection