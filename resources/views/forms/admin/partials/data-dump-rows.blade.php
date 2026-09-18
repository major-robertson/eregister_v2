{{--
    Label/value rows from App\Domains\Forms\Admin\Support\ApplicationDataDump.
    A row is either a value (label + value) or a list of items (repeater
    rows, per-person extras); each item holds titled blocks of more rows,
    rendered by including this partial again.

    Inputs: $rows (list of rows).
--}}
<dl class="divide-y divide-zinc-100">
    @foreach ($rows as $row)
        @if (isset($row['items']))
            <div class="py-3">
                <dt class="text-sm text-text-secondary">{{ $row['label'] }} ({{ count($row['items']) }})</dt>
                <dd class="mt-2 space-y-3">
                    @foreach ($row['items'] as $item)
                        <div class="rounded-lg border border-border px-4 py-3">
                            <p class="text-sm font-semibold text-text-primary">{{ $item['title'] }}</p>
                            @foreach ($item['blocks'] as $block)
                                @if ($block['title'])
                                    <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-text-secondary">{{ $block['title'] }}</p>
                                @endif
                                @include('forms.admin.partials.data-dump-rows', ['rows' => $block['rows']])
                            @endforeach
                        </div>
                    @endforeach
                </dd>
            </div>
        @else
            <div class="grid grid-cols-1 gap-x-4 gap-y-0.5 py-2 sm:grid-cols-5">
                <dt class="text-sm text-text-secondary sm:col-span-3">{{ $row['label'] }}</dt>
                <dd class="whitespace-pre-line break-words text-sm font-medium text-text-primary sm:col-span-2">{{ $row['value'] }}</dd>
            </div>
        @endif
    @endforeach
</dl>
