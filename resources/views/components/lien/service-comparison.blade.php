@props([
    // ServiceComparison::rows(): label, self_serve, full_service
    'rows',
    // ServiceComparison::prices(): cents per service level, or null to leave prices out
    'prices' => null,
])

{{-- Self-serve next to full service for one document and state (EREG-143) --}}
<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700']) }}>
    <table class="w-full text-left text-sm">
        <thead class="bg-zinc-50 dark:bg-zinc-800">
            <tr>
                <th scope="col" class="w-1/4 px-4 py-3 font-medium text-zinc-500 dark:text-zinc-400"><span class="sr-only">What</span></th>
                <th scope="col" class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                    Self-serve
                    @if ($prices)
                        <span class="ml-1 font-normal text-zinc-500 dark:text-zinc-400">${{ number_format($prices['self_serve'] / 100) }}</span>
                    @endif
                </th>
                <th scope="col" class="px-4 py-3 font-semibold text-zinc-900 dark:text-white">
                    Full service
                    @if ($prices)
                        <span class="ml-1 font-normal text-zinc-500 dark:text-zinc-400">${{ number_format($prices['full_service'] / 100) }}</span>
                    @endif
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @foreach ($rows as $row)
                <tr>
                    <th scope="row" class="px-4 py-3 align-top font-medium text-zinc-700 dark:text-zinc-300">{{ $row['label'] }}</th>
                    <td class="px-4 py-3 align-top text-zinc-600 dark:text-zinc-400">{{ $row['self_serve'] }}</td>
                    <td class="px-4 py-3 align-top text-zinc-600 dark:text-zinc-400">{{ $row['full_service'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
