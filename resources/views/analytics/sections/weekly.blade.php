<div x-show="activeTab === 'weekly'" 
     x-cloak 
     x-data="siteChurnFilter(@js($summaries['6_site_monthly_churn'] ?? []))"
     x-init="$nextTick(() => initChart())"
     class="space-y-6">

    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-1">Monthly Churn Rate by Site</h2>
        <p class="text-slate-500 dark:text-slate-400 text-xs mb-6">Filter specific sites and timeframes to compare monthly churn trends dynamically.</p>

        <!-- Filter Form -->
        <form @submit.prevent="applyFilter()" class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50 mb-6 space-y-4">
            <div>
                <!-- Header with Select All / Deselect All Controls -->
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400">
                        Filter Sites 
                        <span class="font-normal text-slate-400" x-text="`(${selectedSites.length}/${availableSites.length} selected)`"></span>
                    </label>
                    
                    <div class="flex items-center gap-2 text-xs">
                        <button type="button" @click="selectAll()" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                            Select All
                        </button>
                        <span class="text-slate-300 dark:text-slate-700">|</span>
                        <button type="button" @click="deselectAll()" class="text-slate-500 dark:text-slate-400 hover:underline font-medium">
                            Deselect All
                        </button>
                    </div>
                </div>

                <!-- Checkboxes Container -->
                <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto pr-2">
                    <template x-for="site in availableSites" :key="site">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                            <input type="checkbox" :value="site" x-model="selectedSites" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span x-text="site"></span>
                        </label>
                    </template>
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-4 pt-2 border-t border-slate-200 dark:border-slate-800">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Start Month</label>
                    <input type="month" x-model="tempStart" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-1.5 text-slate-800 dark:text-slate-100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">End Month</label>
                    <input type="month" x-model="tempEnd" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-1.5 text-slate-800 dark:text-slate-100">
                </div>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-xl transition-colors text-sm shadow-sm">
                    Apply Filters
                </button>

                <button type="button" @click="resetFilter()" class="text-xs text-slate-500 dark:text-slate-400 hover:underline py-2">
                    Reset
                </button>
            </div>
        </form>

        <!-- Interactive Multi-Site Canvas -->
        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-4">
            <canvas id="siteChurnChart" class="max-h-[450px] w-full"></canvas>
        </div>
    </div>
</div>

<script>
function siteChurnFilter(siteData) {
    return {
        rawData: siteData,
        availableSites: [],
        selectedSites: [],
        tempStart: '',
        tempEnd: '',
        chart: null,

        initChart() {
            // Extract unique sites
            this.availableSites = [...new Set(this.rawData.map(d => d.Site))].filter(Boolean);
            this.selectedSites = [...this.availableSites]; // Default to all selected

            // Extract unique sorted months
            const months = [...new Set(this.rawData.map(d => d.Month))].filter(Boolean).sort();

            if (months.length > 0) {
                // Find latest month in dataset
                const latestMonth = months[months.length - 1];
                const [year, month] = latestMonth.split('-');

                // Subtract 1 year
                const startYear = parseInt(year, 10) - 1;
                this.tempStart = `${startYear}-${month}`;
            }

            this.applyFilter();
        },

        // Helper functions for Select / Deselect actions
        selectAll() {
            this.selectedSites = [...this.availableSites];
        },

        deselectAll() {
            this.selectedSites = [];
        },

        applyFilter() {
            let filtered = this.rawData;

            if (this.selectedSites.length > 0) {
                filtered = filtered.filter(d => this.selectedSites.includes(d.Site));
            } else {
                // If no sites selected, pass an empty dataset
                filtered = [];
            }

            if (this.tempStart) {
                filtered = filtered.filter(d => d.Month >= this.tempStart);
            }
            if (this.tempEnd) {
                filtered = filtered.filter(d => d.Month <= this.tempEnd);
            }

            this.renderChart(filtered);
        },

        resetFilter() {
            this.selectAll();

            // Recalculate default 1-year window on reset
            const months = [...new Set(this.rawData.map(d => d.Month))].filter(Boolean).sort();
            if (months.length > 0) {
                const latestMonth = months[months.length - 1];
                const [year, month] = latestMonth.split('-');

                const startYear = parseInt(year, 10) - 1;
                this.tempStart = `${startYear}-${month}`;
            } else {
                this.tempStart = '';
                this.tempEnd = '';
            }

            this.applyFilter();
        },

        renderChart(data) {
            if (this.chart) this.chart.destroy();

            const months = [...new Set(data.map(d => d.Month))].filter(Boolean).sort();

            const colors = ['#6366f1', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#3b82f6', '#f97316', '#14b8a6'];

            const datasets = this.selectedSites.map((site, idx) => ({
                label: site,
                data: months.map(m => {
                    const row = data.find(d => d.Site === site && d.Month === m);
                    return row ? row['Monthly Churn Percentage'] : 0;
                }),
                borderColor: colors[idx % colors.length],
                tension: 0.2
            }));

            this.chart = new Chart(document.getElementById('siteChurnChart'), {
                type: 'line',
                data: { labels: months, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            title: { display: true, text: 'Churn Percentage (%)' },
                            ticks: { callback: v => v + '%' } 
                        }
                    }
                }
            });
        }
    }
}
</script>