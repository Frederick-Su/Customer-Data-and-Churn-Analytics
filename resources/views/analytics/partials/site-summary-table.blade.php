@if(!empty($summary))
@php
    $customers = $summary['Customers'] ?? [];
@endphp

<div class="mt-4 border-t border-graphite-200 dark:border-graphite-800 pt-4">
    <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500 mb-2">{{ $title ?? 'Summary' }}</h3>
    <div class="max-h-[180px] overflow-y-auto border border-graphite-200 dark:border-graphite-800 rounded-sm">
        <table class="w-full text-xs text-left font-mono text-graphite-600 dark:text-graphite-300">
            <thead class="bg-graphite-100 dark:bg-graphite-800/60 text-graphite-500 dark:text-graphite-400 uppercase tracking-wider sticky top-0">
                <tr>
                    <th class="p-2.5">Site</th>
                    <th class="p-2.5">Customers</th>
                    <th class="p-2.5">Avg Tenure (Mo.)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-graphite-200 dark:divide-graphite-800">
                @foreach($customers as $site => $count)
                <tr>
                    <td class="p-2.5 font-medium text-graphite-800 dark:text-graphite-100">{{ $site }}</td>
                    <td class="p-2.5">{{ $count }}</td>
                    <td class="p-2.5">{{ $summary['Avg_Tenure'][$site] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif