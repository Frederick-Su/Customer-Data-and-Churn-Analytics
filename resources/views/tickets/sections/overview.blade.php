<div x-show="activeTab === 'overview'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    
    @if(isset($summaries['8_monthly_volume']))
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm lg:col-span-2">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Monthly Ticket Volume Trend</h3>
        </div>
        <div class="p-5" x-data="dateFilteredChart(@js($summaries['8_monthly_volume']), 'Creation Time', 'Ticket ID', 'line', false)">
            
            <!-- Date Filter UI -->
            <form @submit.prevent="applyFilter()" class="flex flex-wrap items-end gap-4 mb-6 bg-graphite-50 dark:bg-graphite-950/40 p-3 rounded-sm border border-graphite-200 dark:border-graphite-800">
                <div>
                    <label class="block font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">Start Month</label>
                    <input type="month" x-model="tempStart" class="cursor-pointer font-mono text-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 rounded-sm px-3 py-1.5 text-graphite-800 dark:text-graphite-100 focus:outline-none focus:ring-1 focus:ring-signal-500 dark:[color-scheme:dark]">
                </div>
                <div>
                    <label class="block font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">End Month</label>
                    <input type="month" x-model="tempEnd" class="cursor-pointer font-mono text-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 rounded-sm px-3 py-1.5 text-graphite-800 dark:text-graphite-100 focus:outline-none focus:ring-1 focus:ring-signal-500 dark:[color-scheme:dark]">
                </div>
                <button type="submit" class="bg-signal-600 hover:bg-signal-700 dark:bg-signal-500 dark:hover:bg-signal-400 text-paper-50 dark:text-graphite-950 font-mono font-semibold uppercase tracking-wider px-4 py-1.5 rounded-sm transition-colors text-xs">
                    Apply
                </button>
                <button type="button" @click="resetFilter()" class="font-mono text-[11px] uppercase tracking-wider text-graphite-500 dark:text-graphite-400 hover:text-signal-600 hover:underline py-1.5">
                    Reset
                </button>
            </form>

            <div class="relative w-full" style="height: 350px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>
    @endif

    @if(isset($summaries['1_tickets_by_area']))
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Tickets by Area</h3>
        </div>
        <div class="p-5" x-data="areaFilteredChart(@js($summaries['1_tickets_by_area']), 'bar', false)">
            
            <!-- Date Period Filter -->
            <form @submit.prevent="applyDateFilter()" class="flex flex-wrap items-end gap-3 mb-5 bg-graphite-50 dark:bg-graphite-950/40 p-3 rounded-sm border border-graphite-200 dark:border-graphite-800">
                <div>
                    <label class="block font-mono text-[10px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">Start Month</label>
                    <input type="month" x-model="tempStart" class="cursor-pointer font-mono text-xs bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 rounded-sm px-2.5 py-1 text-graphite-800 dark:text-graphite-100 focus:outline-none focus:ring-1 focus:ring-signal-500 dark:[color-scheme:dark]">
                </div>
                <div>
                    <label class="block font-mono text-[10px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">End Month</label>
                    <input type="month" x-model="tempEnd" class="cursor-pointer font-mono text-xs bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 rounded-sm px-2.5 py-1 text-graphite-800 dark:text-graphite-100 focus:outline-none focus:ring-1 focus:ring-signal-500 dark:[color-scheme:dark]">
                </div>
                <button type="submit" class="bg-signal-600 hover:bg-signal-700 dark:bg-signal-500 dark:hover:bg-signal-400 text-paper-50 dark:text-graphite-950 font-mono font-semibold uppercase tracking-wider px-3 py-1 rounded-sm transition-colors text-[11px]">
                    Apply
                </button>
                <button type="button" @click="resetDateFilter()" class="font-mono text-[10px] uppercase tracking-wider text-graphite-500 dark:text-graphite-400 hover:text-signal-600 hover:underline py-1">
                    Reset
                </button>
            </form>

            <!-- Area Checkbox Filters -->
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

            <div class="relative w-full" style="height: 280px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>
    @endif

    @if(isset($summaries['2_tickets_by_complaint']))
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Tickets by Complaint Type</h3>
        </div>
        <div class="p-5" x-data="standardTicketChart(@js($summaries['2_tickets_by_complaint']), 'Type Complaint', 'Ticket_Count', 'bar', true)">
            <div class="relative w-full" style="height: 350px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>
    @endif

    @if(isset($summaries['7_complaint_proportion']))
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm lg:col-span-2">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Proportion of Tickets by Complaint</h3>
        </div>
        <div class="p-5" x-data="complaintPieChart(@js($summaries['7_complaint_proportion']))">
            
            <!-- Date Period Filter UI -->
            <form @submit.prevent="applyFilter()" class="flex flex-wrap items-end gap-4 mb-6 bg-graphite-50 dark:bg-graphite-950/40 p-3 rounded-sm border border-graphite-200 dark:border-graphite-800">
                <div>
                    <label class="block font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">Start Month</label>
                    <input type="month" x-model="tempStart" class="cursor-pointer font-mono text-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 rounded-sm px-3 py-1.5 text-graphite-800 dark:text-graphite-100 focus:outline-none focus:ring-1 focus:ring-signal-500 dark:[color-scheme:dark]">
                </div>
                <div>
                    <label class="block font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">End Month</label>
                    <input type="month" x-model="tempEnd" class="cursor-pointer font-mono text-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 rounded-sm px-3 py-1.5 text-graphite-800 dark:text-graphite-100 focus:outline-none focus:ring-1 focus:ring-signal-500 dark:[color-scheme:dark]">
                </div>
                <button type="submit" class="bg-signal-600 hover:bg-signal-700 dark:bg-signal-500 dark:hover:bg-signal-400 text-paper-50 dark:text-graphite-950 font-mono font-semibold uppercase tracking-wider px-4 py-1.5 rounded-sm transition-colors text-xs">
                    Apply
                </button>
                <button type="button" @click="resetFilter()" class="font-mono text-[11px] uppercase tracking-wider text-graphite-500 dark:text-graphite-400 hover:text-signal-600 hover:underline py-1.5">
                    Reset
                </button>
            </form>

            <div class="relative w-full flex justify-center" style="height: 380px;">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>
    </div>
    @endif
</div>