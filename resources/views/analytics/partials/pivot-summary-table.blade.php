@if(!empty($summary))
@php
    $stats = array_keys($summary);
    $regions = array_keys(reset($summary));
@endphp

<div class="mt-4 border-t border-graphite-200 dark:border-graphite-800 pt-4">
    <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500 mb-2">{{ $title ?? 'Summary' }}</h3>
    <div class="overflow-x-auto border border-graphite-200 dark:border-graphite-800 rounded-sm">
        <table class="w-full text-xs text-left font-mono text-graphite-600 dark:text-graphite-300">
            <thead class="bg-graphite-100 dark:bg-graphite-800/60 text-graphite-500 dark:text-graphite-400 uppercase tracking-wider">
                <tr>
                    <th class="p-2.5">Metric</th>
                    @foreach($stats as $stat)
                        <th class="p-2.5">{{ ucfirst($stat) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-graphite-200 dark:divide-graphite-800">
                @foreach($regions as $region)
                <tr>
                    <td class="p-2.5 font-semibold text-graphite-800 dark:text-graphite-100">{{ $region }}</td>
                    @foreach($summary as $stat => $values)
                        <td class="p-2.5">{{ $values[$region] ?? '-' }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif