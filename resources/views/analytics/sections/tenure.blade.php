<div x-show="activeTab === 'tenure'" x-cloak class="grid grid-cols-1 lg:grid-cols-1 gap-6">
    @if(!empty($summaries['2_tenure_records']))
    <div
        x-data='tenureScatter(@json($summaries["2_tenure_records"]))'
        x-init="init()"
        class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm flex flex-col justify-between transition-colors"
    >
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800 flex items-center justify-between">
            <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Customer Tenure by Site</h3>
            <span class="font-mono text-[11px] text-graphite-400 dark:text-graphite-500" x-text="filteredRecords().length + ' shown'"></span>
        </div>

        <div class="p-5">
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 mb-4">

                <!-- Region filter -->
                <div class="sm:w-40 shrink-0">
                    <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500 mb-2">Region</p>
                    <div class="space-y-1">
                        <template x-for="region in regions" :key="region">
                            <label class="flex items-center gap-2 text-sm text-graphite-600 dark:text-graphite-300 cursor-pointer">
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

                <!-- Site filter -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-2">
                        <p class="font-mono text-[11px] uppercase tracking-wider text-graphite-400 dark:text-graphite-500">Site</p>
                        <div class="flex gap-2">
                            <button type="button" @click="selectAllSites()" class="font-mono text-[11px] uppercase text-signal-600 dark:text-signal-400 hover:underline">All</button>
                            <button type="button" @click="clearAllSites()" class="font-mono text-[11px] uppercase text-graphite-400 hover:underline">None</button>
                        </div>
                    </div>

                    <input type="text" x-model="siteSearch" placeholder="Search sites…"
                           class="w-full text-xs font-mono mb-2 px-2 py-1.5 rounded-sm border border-graphite-200 dark:border-graphite-700 bg-graphite-50 dark:bg-graphite-950/40 text-graphite-600 dark:text-graphite-300 focus:outline-none focus:ring-1 focus:ring-signal-500">

                    <div class="max-h-32 overflow-y-auto grid grid-cols-2 gap-x-3 gap-y-1 pr-1">
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

            <!-- Chart -->
            <div class="rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-3" style="height: 340px;">
                <canvas x-ref="tenureCanvas"></canvas>
            </div>
        </div>

        <div class="px-5 pb-5">
            @include('analytics.partials.pivot-summary-table', [
                'summary' => $summaries['2_tenure'] ?? null,
                'title' => 'Regional Metrics Summary',
            ])
        </div>
    </div>
    @endif

    @if($imgLoyalty)
    <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm flex flex-col justify-between transition-colors">
        <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
            <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Average Customer Loyalty by Site</h3>
        </div>
        <div class="p-5">
            @include('analytics.partials.chart-panel', [
                'image' => $imgLoyalty,
                'modal' => 'Average Customer Loyalty by Site',
                'alt' => 'Site Loyalty',
                'boxClass' => 'rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-2 mb-4 cursor-pointer group',
                'imageClass' => 'w-full h-auto object-contain max-h-[350px] mx-auto rounded-sm group-hover:opacity-90 transition-opacity',
            ])

            @include('analytics.partials.site-summary-table', [
                'summary' => $summaries['3_site_loyalty'] ?? null,
                'title' => 'Site Loyalty Breakdown',
            ])
        </div>
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
            'JAKARTA': '#D98E2B',
            'SUKABUMI': '#3FA76B',
            'BANDUNG': '#4A6C8C',
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
                backgroundColor: (this.colors[region] || '#7A828C') + 'B3',
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