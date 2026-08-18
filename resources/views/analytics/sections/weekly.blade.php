<div x-show="activeTab === 'weekly'" 
     x-cloak 
     x-data="siteChurnFilter(@js($summaries['6_site_monthly_churn'] ?? []))"
     x-init="$nextTick(() => initChart())"
     class="space-y-6">

    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
        <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-1">Monthly Churn Rate by Site</h2>
        <p class="text-slate-500 dark:text-slate-400 text-xs mb-6">Filter specific regions, sites, and timeframes to compare monthly churn trends dynamically.</p>

        <form @submit.prevent="applyFilter()" class="bg-slate-50 dark:bg-slate-900/60 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50 mb-6 space-y-4">
            
            <div class="flex flex-col sm:flex-row gap-4">

                <template x-if="regions.length > 0">
                    <div class="sm:w-40 shrink-0 border-b sm:border-b-0 sm:border-r border-slate-200 dark:border-slate-800 pb-3 sm:pb-0 sm:pr-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Region</p>
                        <div class="space-y-1 max-h-36 overflow-y-auto pr-1">
                            <template x-for="region in regions" :key="region">
                                <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox"
                                           :value="region"
                                           :checked="selectedRegions.includes(region)"
                                           @change="toggleRegion(region)"
                                           class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500">
                                    <span x-text="region"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            Site 
                            <span class="font-normal text-slate-400 normal-case" x-text="`(${selectedSites.length}/${availableSites.length} selected)`"></span>
                        </p>
                        <div class="flex gap-2">
                            <button type="button" @click="selectAllSites()" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium">All</button>
                            <span class="text-slate-300 dark:text-slate-700">|</span>
                            <button type="button" @click="clearAllSites()" class="text-xs text-slate-400 hover:underline">None</button>
                        </div>
                    </div>

                    <input type="text" 
                           x-model="siteSearch" 
                           placeholder="Search sites..."
                           class="w-full text-xs mb-2 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">

                    <div class="max-h-32 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 gap-x-3 gap-y-1.5 pr-1">
                        <template x-for="site in filteredSiteOptions()" :key="site">
                            <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300 cursor-pointer truncate">
                                <input type="checkbox"
                                       :value="site"
                                       :checked="selectedSites.includes(site)"
                                       @change="toggleSite(site)"
                                       class="rounded border-slate-300 dark:border-slate-600 text-indigo-600 focus:ring-indigo-500 shrink-0">
                                <span x-text="site" class="truncate"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-4 pt-3 border-t border-slate-200 dark:border-slate-800">
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

        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-4">
            <canvas id="siteChurnChart" class="max-h-[450px] w-full"></canvas>
        </div>
    </div>
</div>

<script>
function siteChurnFilter(siteData) {
    return {
        rawData: siteData,
        regions: [],
        selectedRegions: [],
        availableSites: [],
        selectedSites: [],
        siteSearch: '',
        tempStart: '',
        tempEnd: '',
        chart: null,

        initChart() {
            // Extract unique regions (if region field is present)
            this.regions = [...new Set(this.rawData.map(d => d.Region))].filter(Boolean).sort();
            this.selectedRegions = [...this.regions];

            // Extract unique sites
            this.availableSites = [...new Set(this.rawData.map(d => d.Site))].filter(Boolean).sort();
            this.selectedSites = [...this.availableSites];

            // Calculate default 1-year window
            const months = [...new Set(this.rawData.map(d => d.Month))].filter(Boolean).sort();
            if (months.length > 0) {
                const latestMonth = months[months.length - 1];
                const [year, month] = latestMonth.split('-');

                const startYear = parseInt(year, 10) - 1;
                this.tempStart = `${startYear}-${month}`;
                this.tempEnd = latestMonth;
            }

            this.applyFilter();
        },

        // Search + Region filter for site checkboxes list
        filteredSiteOptions() {
            let sites = this.availableSites;

            // Filter sites by selected regions (if data includes Region property)
            if (this.regions.length > 0 && this.selectedRegions.length < this.regions.length) {
                const regionSites = new Set(
                    this.rawData
                        .filter(d => this.selectedRegions.includes(d.Region))
                        .map(d => d.Site)
                );
                sites = sites.filter(s => regionSites.has(s));
            }

            // Filter by search query
            if (this.siteSearch) {
                const query = this.siteSearch.toLowerCase();
                sites = sites.filter(s => s.toLowerCase().includes(query));
            }

            return sites;
        },

        toggleRegion(region) {
            const idx = this.selectedRegions.indexOf(region);
            if (idx > -1) {
                this.selectedRegions.splice(idx, 1);
            } else {
                this.selectedRegions.push(region);
            }
        },

        toggleSite(site) {
            const idx = this.selectedSites.indexOf(site);
            if (idx > -1) {
                this.selectedSites.splice(idx, 1);
            } else {
                this.selectedSites.push(site);
            }
        },

        selectAllSites() {
            // Select all sites currently visible in the search/region filter
            const visibleSites = this.filteredSiteOptions();
            this.selectedSites = [...new Set([...this.selectedSites, ...visibleSites])];
        },

        clearAllSites() {
            this.selectedSites = [];
        },

        applyFilter() {
            let filtered = this.rawData;

            // Region filter
            if (this.regions.length > 0 && this.selectedRegions.length > 0) {
                filtered = filtered.filter(d => !d.Region || this.selectedRegions.includes(d.Region));
            }

            // Site filter
            if (this.selectedSites.length > 0) {
                filtered = filtered.filter(d => this.selectedSites.includes(d.Site));
            } else {
                filtered = [];
            }

            // Date filters
            if (this.tempStart) {
                filtered = filtered.filter(d => d.Month >= this.tempStart);
            }
            if (this.tempEnd) {
                filtered = filtered.filter(d => d.Month <= this.tempEnd);
            }

            this.renderChart(filtered);
        },

        resetFilter() {
            this.selectedRegions = [...this.regions];
            this.selectedSites = [...this.availableSites];
            this.siteSearch = '';

            const months = [...new Set(this.rawData.map(d => d.Month))].filter(Boolean).sort();
            if (months.length > 0) {
                const latestMonth = months[months.length - 1];
                const [year, month] = latestMonth.split('-');

                const startYear = parseInt(year, 10) - 1;
                this.tempStart = `${startYear}-${month}`;
                this.tempEnd = latestMonth;
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
    };
}
</script>