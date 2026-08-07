@if(!empty($summary))
@php
    $customers = $summary['Customers'] ?? [];
@endphp

<div class="mt-4 border-t border-slate-100 dark:border-slate-700/60 pt-4">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-2">{{ $title ?? 'Summary' }}</h3>
    <div class="max-h-[180px] overflow-y-auto">
        <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-100 dark:bg-slate-700/50 text-slate-700 dark:text-slate-200 font-medium sticky top-0">
                <tr>
                    <th class="p-2">Site</th>
                    <th class="p-2">Customers</th>
                    <th class="p-2">Avg Tenure (Months)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach($customers as $site => $count)
                <tr>
                    <td class="p-2 font-medium text-slate-800 dark:text-slate-100">{{ $site }}</td>
                    <td class="p-2">{{ $count }}</td>
                    <td class="p-2">{{ $summary['Avg_Tenure'][$site] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif