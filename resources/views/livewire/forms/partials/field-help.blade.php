@php
    /**
     * Render a field's help text, linkifying any http(s) URLs as anchor tags.
     *
     * Help text is authored in PHP form definitions (never user input), so
     * the escape-then-linkify pattern is safe: e() escapes everything first
     * and only well-formed http(s)://… runs are wrapped in <a>.
     *
     * Variables in scope:
     *   - $help (string): the help text to render
     *   - $variant (string|null): 'description' (default, used by text/select/
     *     date/percent inside flux:field) or 'text' (used by checkbox where
     *     flux:description doesn't fit the inline-label layout).
     */
    $variant = $variant ?? 'description';
    $linkified = preg_replace(
        '/(https?:\/\/[^\s<]+)/',
        '<a href="$1" target="_blank" rel="noopener noreferrer" class="underline hover:no-underline">$1</a>',
        e($help)
    );
@endphp

@if ($variant === 'text')
    <flux:text class="text-sm text-neutral-500">{!! $linkified !!}</flux:text>
@else
    <flux:description>{!! $linkified !!}</flux:description>
@endif
