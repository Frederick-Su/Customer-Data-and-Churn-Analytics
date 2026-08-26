<div x-show="activeTab === 'activity'" 
     x-cloak 
     x-data="activityFilter(@js($summaries['4_active_customers'] ?? []), @js($summaries['5_churn_percentage'] ?? []))"
     x-init="$nextTick(() => initCharts())"
     class="space-y-6">

    <!-- Interactive Filter Form -->
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm transition-colors">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Filter Controls</h3>
        </div>
        <form @submit.prevent="applyFilter()" class="p-5 space-y-4">
            
            <!-- Region Checkbox Section -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500">
                        Regions 
                        <span class="font-normal text-graphite-400 normal-case" x-text="`(${selectedRegions.length}/${availableRegions.length} selected)`"></span>
                    </label>
                    
                    <div class="flex items-center gap-2 font-mono text-[11px] uppercase">
                        <button type="button" @click="selectAllRegions()" class="text-signal-600 dark:text-signal-400 hover:underline font-medium">
                            All
                        </button>
                        <span class="text-graphite-300 dark:text-graphite-700">|</span>
                        <button type="button" @click="deselectAllRegions()" class="text-graphite-500 dark:text-graphite-400 hover:underline font-medium">
                            None
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto pr-2">
                    <template x-for="region in availableRegions" :key="region">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-sm text-xs font-mono bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-700 cursor-pointer text-graphite-700 dark:text-graphite-300 hover:border-signal-600 dark:hover:border-signal-500 transition-colors">
                            <input type="checkbox" :value="region" x-model="selectedRegions" class="rounded-sm border-graphite-300 text-signal-600 focus:ring-signal-500">
                            <span x-text="region"></span>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Date Inputs & Buttons -->
            <div class="flex flex-wrap items-end gap-4 pt-3 border-t border-graphite-200 dark:border-graphite-800">
                <div>
                    <label class="block font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">Start Month</label>
                    <input type="month" x-model="tempStart" @click="$el.showPicker && $el.showPicker()"
                           class="cursor-pointer font-mono text-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-700 rounded-sm px-3 py-2 text-graphite-800 dark:text-graphite-100 focus:outline-none focus:ring-1 focus:ring-signal-500">
                </div>

                <div>
                    <label class="block font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">End Month</label>
                    <input type="month" x-model="tempEnd" @click="$el.showPicker && $el.showPicker()"
                           class="cursor-pointer font-mono text-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-700 rounded-sm px-3 py-2 text-graphite-800 dark:text-graphite-100 focus:outline-none focus:ring-1 focus:ring-signal-500">
                </div>

                <button type="submit" class="bg-signal-600 hover:bg-signal-700 dark:bg-signal-500 dark:hover:bg-signal-400 text-paper-50 dark:text-graphite-950 font-mono font-semibold uppercase tracking-wider px-5 py-2 rounded-sm transition-colors text-xs">
                    Apply Filters
                </button>

                <button type="button" @click="resetFilter()" class="font-mono text-[11px] uppercase tracking-wider text-graphite-500 dark:text-graphite-400 hover:text-signal-600 dark:hover:text-signal-400 hover:underline py-2">
                    Reset Date
                </button>
            </div>
        </form>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Active Customers Chart -->
        <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm transition-colors">
            <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Active Customers Over Time</h3>
            </div>
            <div class="p-4">
                <div class="rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-3">
                    <canvas id="activeCustomersChart" class="max-h-[380px] w-full"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Churn Chart -->
        <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm transition-colors">
            <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Monthly Churn Percentage</h3>
            </div>
            <div class="p-4">
                <div class="rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-3">
                    <canvas id="churnPercentageChart" class="max-h-[380px] w-full"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function activityFilter(rawActive, rawChurn) {
    return {
        rawDataActive: rawActive,
        rawDataChurn: rawChurn,
        availableRegions: [],
        selectedRegions: [],
        tempStart: '',
        tempEnd: '',
        activeChart: null,
        churnChart: null,

        initCharts() {
            // Extract unique regions from active and churn datasets
            const activeRegions = this.rawDataActive.map(d => d.Region);
            const churnRegions = this.rawDataChurn.map(d => d.Region);
            this.availableRegions = [...new Set([...activeRegions, ...churnRegions])].filter(Boolean);
            
            // Default to selecting all regions
            this.selectedRegions = [...this.availableRegions];

            this.renderCharts(this.rawDataActive, this.rawDataChurn);
        },

        selectAllRegions() {
            this.selectedRegions = [...this.availableRegions];
        },

        deselectAllRegions() {
            this.selectedRegions = [];
        },

        applyFilter() {
            let active = this.rawDataActive;
            let churn = this.rawDataChurn;

            // 1. Filter by Selected Regions
            if (this.selectedRegions.length > 0) {
                active = active.filter(d => this.selectedRegions.includes(d.Region));
                churn = churn.filter(d => this.selectedRegions.includes(d.Region));
            } else {
                active = [];
                churn = [];
            }

            // 2. Filter by Date Range
            if (this.tempStart) {
                active = active.filter(d => d.Month >= this.tempStart);
                churn = churn.filter(d => d.Month >= this.tempStart);
            }
            if (this.tempEnd) {
                active = active.filter(d => d.Month <= this.tempEnd);
                churn = churn.filter(d => d.Month <= this.tempEnd);
            }

            this.renderCharts(active, churn);
        },

        resetFilter() {
            this.tempStart = '';
            this.tempEnd = '';
            this.renderCharts(this.rawDataActive, this.rawDataChurn);
        },

        renderCharts(activeData, churnData) {
            if (this.activeChart) this.activeChart.destroy();
            if (this.churnChart) this.churnChart.destroy();

            const regions = [...new Set(activeData.map(d => d.Region))];
            const months = [...new Set(activeData.map(d => d.Month))].sort();
            
            const colors = ['#D98E2B', '#4A6C8C', '#3FA76B', '#C0453A', '#8A6D3B', '#6B8E9E', '#9B8557'];

            // Build Dataset: Active Customers (One line per selected region)
            const activeDatasets = this.selectedRegions.map((region, idx) => ({
                label: region,
                data: months.map(m => {
                    const row = activeData.find(d => d.Region === region && d.Month === m);
                    return row ? row['Active Customers'] : 0;
                }),
                borderColor: colors[idx % colors.length],
                backgroundColor: colors[idx % colors.length],
                tension: 0.3
            }));

            this.activeChart = new Chart(document.getElementById('activeCustomersChart'), {
                type: 'line',
                data: { labels: months, datasets: activeDatasets },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Build Dataset: Churn Percentage (One line per selected region)
            const churnDatasets = this.selectedRegions.map((region, idx) => ({
                label: region,
                data: months.map(m => {
                    const row = churnData.find(d => d.Region === region && d.Month === m);
                    return row ? row['Monthly Churn Percentage'] : 0;
                }),
                borderColor: colors[idx % colors.length],
                backgroundColor: colors[idx % colors.length],
                tension: 0.3
            }));

            this.churnChart = new Chart(document.getElementById('churnPercentageChart'), {
                type: 'line',
                data: { labels: months, datasets: churnDatasets },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    scales: { y: { ticks: { callback: v => v + '%' } } }
                }
            });
        }
    }
}
</script>