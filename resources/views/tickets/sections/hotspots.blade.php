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
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm"
         x-data="vnIdHotspotChart(@js($summaries['3_top_vn_ids']))">
        
        <!-- Header & Date Filter Controls -->
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Top 20 VN IDs (Repeat Complainers)</h3>
            
            <form @submit.prevent="applyDateFilter()" class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-1.5 bg-paper-50 dark:bg-graphite-950 px-2 py-1 rounded-sm border border-graphite-200 dark:border-graphite-800">
                    <label class="font-mono text-[10px] uppercase text-graphite-400">From</label>
                    <input type="month" 
                       x-model="tempStart" 
                       @click="$el.showPicker && $el.showPicker()" 
                       class="bg-transparent font-mono text-xs text-graphite-800 dark:text-graphite-200 focus:outline-none cursor-pointer">
                </div>
                <div class="flex items-center gap-1.5 bg-paper-50 dark:bg-graphite-950 px-2 py-1 rounded-sm border border-graphite-200 dark:border-graphite-800">
                    <label class="font-mono text-[10px] uppercase text-graphite-400">To</label>
                    <input type="month" 
                       x-model="tempEnd" 
                       @click="$el.showPicker && $el.showPicker()" 
                       class="bg-transparent font-mono text-xs text-graphite-800 dark:text-graphite-200 focus:outline-none cursor-pointer">
                </div>
                <button type="submit" class="px-3 py-1 bg-signal-600 hover:bg-signal-700 text-paper-50 font-mono text-xs uppercase rounded-sm transition-colors">Filter</button>
                <button type="button" @click="resetDateFilter()" class="px-3 py-1 border border-graphite-300 dark:border-graphite-700 font-mono text-xs uppercase text-graphite-500 dark:text-graphite-400 rounded-sm hover:border-signal-600 transition-colors">Reset</button>
            </form>
        </div>

        <div class="p-5">
            <div class="relative w-full" style="height: 500px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>