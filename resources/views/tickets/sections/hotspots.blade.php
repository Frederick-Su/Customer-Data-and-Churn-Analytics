<div x-show="activeTab === 'hotspots'" x-cloak class="grid grid-cols-1 gap-5">
    
    @if(isset($imgHeatmap))
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Complaint Concentration across Areas</h3>
        </div>
        <div class="p-4">
            <img src="{{ $imgHeatmap['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90 transition-opacity" @click="openModal('{{ $imgHeatmap['url'] }}', 'Heatmap')">
        </div>
    </div>
    @endif

    @if(isset($summaries['3_top_vn_ids']))
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Top 20 VN IDs (Repeat Complainers)</h3>
        </div>
        <div class="p-5" x-data="standardTicketChart(@js($summaries['3_top_vn_ids']), '', '', 'bar', true)">
            <div class="relative w-full" style="height: 500px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>