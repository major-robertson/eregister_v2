@props(['id' => null])

@unless(app()->environment('testing'))
    <div {{ $attributes }}>
        <div class="g-recaptcha" data-sitekey="{{ config('services.google.recaptcha.site_key') }}" @if($id) id="{{ $id }}" @endif></div>

        @error('g-recaptcha-response')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @error('recaptchaToken')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @once
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endonce
@endunless
