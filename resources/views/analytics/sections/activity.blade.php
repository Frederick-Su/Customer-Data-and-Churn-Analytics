<div x-show="activeTab === 'activity'" 
     x-cloak 
     x-data="activityFilter(@js($summaries['4_active_customers'] ?? []), @js($summaries['5_churn_percentage'] ?? []))"
     x-init="$nextTick(() => initCharts())"
     class="space-y-6">

    <!-- Interactive Filter Form -->
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <form @submit.prevent="applyFilter()" class="space-y-4">
            
            <!-- Region Checkbox Section -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">
                        Filter Regions 
                        <span class="font-normal text-slate-400" x-text="`(${selectedRegions.length}/${availableRegions.length} selected)`"></span>
                    </label>
                    
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" @click="selectAllRegions()" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                            Select All
                        </button>
                        <span class="text-slate-300 dark:text-slate-700">|</span>
                        <button type="button" @click="deselectAllRegions()" class="text-slate-500 dark:text-slate-400 hover:underline font-medium">
                            Deselect All
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto pr-2">
                    <template x-for="region in availableRegions" :key="region">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 cursor-pointer text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors">
                            <input type="checkbox" :value="region" x-model="selectedRegions" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span x-text="region"></span>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Date Inputs & Buttons -->
            <div class="flex flex-wrap items-end gap-4 pt-3 border-t border-slate-100 dark:border-slate-700/50">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Start Month</label>
                    <input type="month" x-model="tempStart" @click="$el.showPicker && $el.showPicker()"
                           class="cursor-pointer bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">End Month</label>
                    <input type="month" x-model="tempEnd" @click="$el.showPicker && $el.showPicker()"
                           class="cursor-pointer bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-xl transition-colors text-sm shadow-sm">
                    Apply Filters
                </button>

                <button type="button" @click="resetFilter()" class="text-xs text-slate-500 dark:text-slate-400 hover:underline py-2">
                    Reset Date
                </button>
            </div>
        </form>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Customers Chart -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Active Customers Over Time</h2>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-3">
                <canvas id="activeCustomersChart" class="max-h-[380px] w-full"></canvas>
            </div>
        </div>

        <!-- Monthly Churn Chart -->
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Monthly Churn Percentage</h2>
            <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-3">
                <canvas id="churnPercentageChart" class="max-h-[380px] w-full"></canvas>
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
            
            const colors = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#3b82f6', '#f97316'];

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