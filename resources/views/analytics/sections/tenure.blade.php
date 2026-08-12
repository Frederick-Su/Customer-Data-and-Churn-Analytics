<div x-show="activeTab === 'tenure'" x-cloak class="grid grid-cols-1 lg:grid-cols-1 gap-6">
    @if(!empty($summaries['2_tenure_records']))
    <div
        x-data='tenureScatter(@json($summaries["2_tenure_records"]))'
        x-init="init()"
        class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between transition-colors"
    >
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100">Customer Tenure by Site</h2>
                <span class="text-xs text-slate-400" x-text="filteredRecords().length + ' customers shown'"></span>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 mb-4">

                <!-- Region filter -->
                <div class="sm:w-40 shrink-0">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Region</p>
                    <div class="space-y-1">
                        <template x-for="region in regions" :key="region">
                            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300 cursor-pointer">
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

                <!-- Site filter -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Site</p>
                        <div class="flex gap-2">
                            <button type="button" @click="selectAllSites()" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">All</button>
                            <button type="button" @click="clearAllSites()" class="text-xs text-slate-400 hover:underline">None</button>
                        </div>
                    </div>

                    <input type="text" x-model="siteSearch" placeholder="Search sites..."
                           class="w-full text-xs mb-2 px-2 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/60 text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-indigo-500">

                    <div class="max-h-32 overflow-y-auto grid grid-cols-2 gap-x-3 gap-y-1 pr-1">
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

            <!-- Chart -->
            <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-3" style="height: 340px;">
                <canvas x-ref="tenureCanvas"></canvas>
            </div>
        </div>

        @include('analytics.partials.pivot-summary-table', [
            'summary' => $summaries['2_tenure'] ?? null,
            'title' => 'Regional Metrics Summary',
        ])
    </div>
    @endif

    @if($imgLoyalty)
    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between transition-colors">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Average Customer Loyalty by Site</h2>
            @include('analytics.partials.chart-panel', [
                'image' => $imgLoyalty,
                'modal' => 'Average Customer Loyalty by Site',
                'alt' => 'Site Loyalty',
                'boxClass' => 'rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 mb-4 cursor-pointer group',
                'imageClass' => 'w-full h-auto object-contain max-h-[350px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200',
            ])
        </div>

        @include('analytics.partials.site-summary-table', [
            'summary' => $summaries['3_site_loyalty'] ?? null,
            'title' => 'Site Loyalty Breakdown',
        ])
    </div>
    @endif
</div>

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tenureScatter', (records) => ({
        records: records || [],
        regions: [],
        sites: [],
        selectedRegions: [],
        selectedSites: [],
        siteSearch: '',
        chart: null,
        colors: {
            'JAKARTA': '#6366f1',
            'SUKABUMI': '#10b981',
            'BANDUNG': '#f59e0b',
        },

        init() {
            this.regions = [...new Set(this.records.map(r => r.Region))].sort();
            this.sites = [...new Set(this.records.map(r => r.Site))].sort();
            this.selectedRegions = [...this.regions];
            this.selectedSites = [...this.sites];

            this.$nextTick(() => {
                this.buildChart();
            });

            this.$watch('selectedRegions', () => this.updateChart());
            this.$watch('selectedSites', () => this.updateChart());
        },

        filteredSiteOptions() {
            if (!this.siteSearch) return this.sites;
            const q = this.siteSearch.toLowerCase();
            return this.sites.filter(s => s.toLowerCase().includes(q));
        },

        toggleRegion(region) {
            const idx = this.selectedRegions.indexOf(region);
            if (idx > -1) this.selectedRegions.splice(idx, 1);
            else this.selectedRegions.push(region);
        },

        toggleSite(site) {
            const idx = this.selectedSites.indexOf(site);
            if (idx > -1) this.selectedSites.splice(idx, 1);
            else this.selectedSites.push(site);
        },

        selectAllSites() {
            this.selectedSites = [...this.sites];
        },

        clearAllSites() {
            this.selectedSites = [];
        },

        filteredRecords() {
            return this.records.filter(r =>
                this.selectedRegions.includes(r.Region) &&
                this.selectedSites.includes(r.Site)
            );
        },

        activeSites() {
            const filtered = this.filteredRecords();
            const present = new Set(filtered.map(r => r.Site));
            return this.sites.filter(s => this.selectedSites.includes(s) && present.has(s));
        },

        buildChart() {
            if (!this.$refs.tenureCanvas) return;

            if (this.chart) {
                this.chart.destroy();
                this.chart = null;
            }

            const ctx = this.$refs.tenureCanvas.getContext('2d');
            const activeSitesList = this.activeSites();
            const byRegion = {};

            this.filteredRecords().forEach(r => {
                const xIndex = activeSitesList.indexOf(r.Site);
                if (xIndex === -1) return;

                const jitter = (Math.random() - 0.5) * 0.5;

                if (!byRegion[r.Region]) byRegion[r.Region] = [];
                byRegion[r.Region].push({
                    x: xIndex + jitter,
                    y: r.Duration_months,
                    site: r.Site,
                });
            });

            const datasets = Object.keys(byRegion).map(region => ({
                label: region,
                data: byRegion[region],
                backgroundColor: (this.colors[region] || '#94a3b8') + 'B3',
                pointRadius: 4,
                pointHoverRadius: 6,
            }));

            this.chart = new Chart(ctx, {
                type: 'scatter',
                data: { datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    scales: {
                        x: {
                            type: 'linear',
                            min: -0.5,
                            max: Math.max(activeSitesList.length - 0.5, 0.5),
                            ticks: {
                                stepSize: 1,
                                autoSkip: false,
                                callback: (val) => {
                                    const idx = Math.round(val);
                                    return activeSitesList[idx] || '';
                                }
                            },
                            title: { display: true, text: 'Site' },
                        },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Tenure (Months)' },
                        },
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                title: () => '',
                                label: (item) => `${item.raw.site} — ${item.dataset.label}: ${item.raw.y.toFixed(1)} mo`,
                            },
                        },
                    },
                },
            });
        },

        updateChart() {
            this.buildChart();
        },
    }));
});
</script>
@endonce