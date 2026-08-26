<div x-show="activeTab === 'weekly'" 
     x-cloak 
     x-data="siteChurnFilter(@js($summaries['6_site_monthly_churn'] ?? []))"
     x-init="$nextTick(() => initChart())"
     class="space-y-6">

    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm transition-colors">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Monthly Churn Rate by Site</h3>
        </div>

        <div class="p-5">
        <p class="text-graphite-500 dark:text-graphite-400 text-xs mb-5">Filter regions, sites, and timeframes to compare monthly churn trends.</p>

        <form @submit.prevent="applyFilter()" class="bg-graphite-50 dark:bg-graphite-950/40 p-4 rounded-sm border border-graphite-200 dark:border-graphite-800 mb-6 space-y-4">
            
            <div class="flex flex-col sm:flex-row gap-4">

                <template x-if="regions.length > 0">
                    <div class="sm:w-40 shrink-0 border-b sm:border-b-0 sm:border-r border-graphite-200 dark:border-graphite-800 pb-3 sm:pb-0 sm:pr-4">
                        <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-2">Region</p>
                        <div class="space-y-1 max-h-36 overflow-y-auto pr-1">
                            <template x-for="region in regions" :key="region">
                                <label class="flex items-center gap-2 text-xs text-graphite-600 dark:text-graphite-300 cursor-pointer">
                                    <input type="checkbox"
                                           :value="region"
                                           :checked="selectedRegions.includes(region)"
                                           @change="toggleRegion(region)"
                                           class="rounded-sm border-graphite-300 dark:border-graphite-600 text-signal-600 focus:ring-signal-500">
                                    <span x-text="region"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500">
                            Site 
                            <span class="font-normal text-graphite-400 normal-case" x-text="`(${selectedSites.length}/${availableSites.length} selected)`"></span>
                        </p>
                        <div class="flex gap-2 font-mono text-[11px] uppercase">
                            <button type="button" @click="selectAllSites()" class="text-signal-600 dark:text-signal-400 hover:underline font-medium">All</button>
                            <span class="text-graphite-300 dark:text-graphite-700">|</span>
                            <button type="button" @click="clearAllSites()" class="text-graphite-400 hover:underline">None</button>
                        </div>
                    </div>

                    <input type="text" 
                           x-model="siteSearch" 
                           placeholder="Search sites…"
                           class="w-full text-xs font-mono mb-2 px-3 py-1.5 rounded-sm border border-graphite-200 dark:border-graphite-700 bg-paper-100 dark:bg-graphite-900 text-graphite-700 dark:text-graphite-200 focus:outline-none focus:ring-1 focus:ring-signal-500">

                    <div class="max-h-32 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 gap-x-3 gap-y-1.5 pr-1">
                        <template x-for="site in filteredSiteOptions()" :key="site">
                            <label class="flex items-center gap-2 text-xs text-graphite-600 dark:text-graphite-300 cursor-pointer truncate">
                                <input type="checkbox"
                                       :value="site"
                                       :checked="selectedSites.includes(site)"
                                       @change="toggleSite(site)"
                                       class="rounded-sm border-graphite-300 dark:border-graphite-600 text-signal-600 focus:ring-signal-500 shrink-0">
                                <span x-text="site" class="truncate"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-end gap-4 pt-3 border-t border-graphite-200 dark:border-graphite-800">
                <div>
                    <label class="block font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">Start Month</label>
                    <input type="month" x-model="tempStart" @click="$el.showPicker && $el.showPicker()" class="cursor-pointer font-mono text-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 rounded-sm px-3 py-1.5 text-graphite-800 dark:text-graphite-100">
                </div>

                <div>
                    <label class="block font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-1">End Month</label>
                    <input type="month" x-model="tempEnd" @click="$el.showPicker && $el.showPicker()" class="cursor-pointer font-mono text-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 rounded-sm px-3 py-1.5 text-graphite-800 dark:text-graphite-100">
                </div>

                <button type="submit" class="bg-signal-600 hover:bg-signal-700 dark:bg-signal-500 dark:hover:bg-signal-400 text-paper-50 dark:text-graphite-950 font-mono font-semibold uppercase tracking-wider px-5 py-2 rounded-sm transition-colors text-xs">
                    Apply Filters
                </button>

                <button type="button" @click="resetFilter()" class="font-mono text-[11px] uppercase tracking-wider text-graphite-500 dark:text-graphite-400 hover:text-signal-600 dark:hover:text-signal-400 hover:underline py-2">
                    Reset Date
                </button>
            </div>
        </form>

        <div class="rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-4">
            <canvas id="siteChurnChart" class="max-h-[450px] w-full"></canvas>
        </div>
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
            const colors = ['#D98E2B', '#4A6C8C', '#3FA76B', '#C0453A', '#8A6D3B', '#6B8E9E', '#9B8557', '#5C7A99'];

            const datasets = this.selectedSites.map((site, idx) => ({
                label: site,
                data: months.map(m => {
                    const row = data.find(d => d.Site === site && d.Month === m);
                    return row ? row['Monthly Churn Percentage'] : null;
                }),
                borderColor: colors[idx % colors.length],
                tension: 0.2,
                spanGaps: false
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