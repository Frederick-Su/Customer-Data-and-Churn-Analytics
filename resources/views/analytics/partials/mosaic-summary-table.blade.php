@if(!empty($summary))
@php
    $columns = array_keys($summary);
    $rows = array_keys(reset($summary));
@endphp

<div class="mt-4 border-t border-slate-100 dark:border-slate-700/60 pt-4">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-2">
        {{ $title ?? 'Summary' }}
    </h3>

    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-100 dark:bg-slate-700/50 text-slate-700 dark:text-slate-200 font-medium">
                <tr>
                    <th class="p-2 rounded-l">Metric</th>

                    @foreach($columns as $column)
                        <th class="p-2">{{ ucfirst($column) }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                @foreach($rows as $row)
                <tr>
                    <td class="p-2 font-semibold text-slate-800 dark:text-slate-100">
                        {{ $row }}
                    </td>

                    @foreach($columns as $column)
                        <td class="p-2">
                            {{ $summary[$column][$row] ?? '-' }}
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif