@extends('layouts.landing')

@section('title', $businessName . ' - Lien Services')

@section('meta')
<link rel="canonical" href="{{ $canonicalUrl }}" />
<meta name="robots" content="noindex, nofollow" />
@endsection

@section('content')
<livewire:marketing.contractor-landing :tracking-link-id="$trackingLinkId" :source="$source" />
@endsection