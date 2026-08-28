<div x-show="activeTab === 'duration'" x-cloak class="grid grid-cols-1 gap-5">
    
    @if(isset($imgDurationDist))
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Ticket Duration Spread by Complaint Type</h3>
        </div>
        <div class="p-4">
            <img src="{{ $imgDurationDist['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90 transition-opacity" @click="openModal('{{ $imgDurationDist['url'] }}', 'Duration Spread')">
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @if(isset($summaries['4_median_duration_area']))
        <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm flex flex-col">
            <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Median Duration by Area</h3>
            </div>
            <div class="p-5 flex-1 flex flex-col" x-data="areaFilteredChart(@js($summaries['4_median_duration_area']), 'Area', 'Duration_Days', 'bar', true)">
                
                <!-- Area Filter UI -->
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500">Filter Areas</p>
                        <div class="flex gap-2">
                            <button type="button" @click="selectAll()" class="font-mono text-[11px] uppercase text-signal-600 dark:text-signal-400 hover:underline">All</button>
                            <button type="button" @click="clearAll()" class="font-mono text-[11px] uppercase text-graphite-400 hover:underline">None</button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2.5 max-h-24 overflow-y-auto">
                        <template x-for="area in availableAreas" :key="area">
                            <label class="flex items-center gap-1.5 text-xs text-graphite-600 dark:text-graphite-300 cursor-pointer bg-graphite-50 dark:bg-graphite-950/40 px-2 py-1 rounded-sm border border-graphite-200 dark:border-graphite-800">
                                <input type="checkbox" :value="area" :checked="selectedAreas.includes(area)" @change="toggleArea(area)" class="rounded-sm border-graphite-300 dark:border-graphite-600 text-signal-600 focus:ring-signal-500 shrink-0">
                                <span x-text="area"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="relative w-full flex-1 min-h-[300px]">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>
        </div>
        @endif

        @if(isset($summaries['5_duration_by_complaint']))
        <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
            <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Median Duration by Complaint Type</h3>
            </div>
            <div class="p-5" x-data="standardTicketChart(@js(array_slice($summaries['5_duration_by_complaint'], 0, 15)), 'Type Complaint', 'Median_Duration', 'bar', true)">
                <div class="relative w-full" style="height: 415px;">
                    <canvas x-ref="canvas"></canvas>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>