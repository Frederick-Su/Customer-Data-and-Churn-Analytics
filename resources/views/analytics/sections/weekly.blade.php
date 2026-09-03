<div x-show="activeTab === 'weekly'" 
     x-cloak 
     x-data="siteChurnFilter(@js($summaries['6_site_monthly_churn'] ?? []))"
     x-init="
        $nextTick(() => initChart());
        $watch('darkMode', () => updateTheme());
    "
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
                    Apply
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
    // Local closure variable — completely unproxied by Alpine's reactivity system
    let chartInstance = null;

    return {
        rawData: siteData || [],
        regions: [],
        selectedRegions: [],
        availableSites: [],
        selectedSites: [],
        siteSearch: '',
        tempStart: '',
        tempEnd: '',
        appliedStart: '',
        appliedEnd: '',

        initChart() {
            if (!this.rawData || this.rawData.length === 0) return;

            // Extract unique regions
            this.regions = [...new Set(this.rawData.map(d => d.Region))].filter(Boolean).sort();
            this.selectedRegions = [...this.regions];

            // Extract unique sites
            this.availableSites = [...new Set(this.rawData.map(d => d.Site))].filter(Boolean).sort();
            this.selectedSites = [...this.availableSites];

            // Calculate default 1-year date window
            const months = [...new Set(this.rawData.map(d => d.Month))].filter(Boolean).sort();
            if (months.length > 0) {
                const latestMonth = months[months.length - 1];
                const [year, month] = latestMonth.split('-');
                const startYear = parseInt(year, 10) - 1;

                this.tempStart = `${startYear}-${month}`;
                this.tempEnd = latestMonth;
            }

            this.appliedStart = this.tempStart;
            this.appliedEnd = this.tempEnd;

            // Build chart on next tick using element ref or ID
            this.$nextTick(() => this.buildChart());
        },

        filteredSiteOptions() {
            let sites = this.availableSites;

            if (this.siteSearch) {
                const query = this.siteSearch.toLowerCase();
                sites = sites.filter(s => s.toLowerCase().includes(query));
            }

            return sites;
        },

        toggleRegion(region) {
            const idx = this.selectedRegions.indexOf(region);
            if (idx > -1) {
                // Unchecking Region: remove this region's sites from selectedSites
                this.selectedRegions.splice(idx, 1);
                const regionSites = this.rawData
                    .filter(d => d.Region === region)
                    .map(d => d.Site);
                this.selectedSites = this.selectedSites.filter(s => !regionSites.includes(s));
            } else {
                // Checking Region: add this region's sites back to selectedSites
                this.selectedRegions.push(region);
                const regionSites = this.rawData
                    .filter(d => d.Region === region)
                    .map(d => d.Site);
                this.selectedSites = [...new Set([...this.selectedSites, ...regionSites])];
            }
            
            this.syncVisibilities();
        },

        toggleSite(site) {
            const idx = this.selectedSites.indexOf(site);
            const isSelected = idx === -1;

            if (isSelected) {
                this.selectedSites.push(site);
            } else {
                this.selectedSites.splice(idx, 1);
            }

            this.updateSingleVisibility(site, isSelected);
        },

        selectAllSites() {
            const visibleSites = this.filteredSiteOptions();
            this.selectedSites = [...new Set([...this.selectedSites, ...visibleSites])];
            this.syncVisibilities();
        },

        clearAllSites() {
            const visibleSites = this.filteredSiteOptions();
            this.selectedSites = this.selectedSites.filter(s => !visibleSites.includes(s));
            this.syncVisibilities();
        },

        // Toggle a single site dataset via local closure reference
        updateSingleVisibility(siteName, isVisible) {
            if (!chartInstance) return;

            const datasetIndex = chartInstance.data.datasets.findIndex(ds => ds.label === siteName);
            if (datasetIndex !== -1) {
                chartInstance.setDatasetVisibility(datasetIndex, isVisible);
                chartInstance.update(); // Operates directly on native Chart.js instance
            }
        },

        // Bulk sync dataset visibilities (used for Region toggles, Select All, Clear All)
        syncVisibilities() {
            if (!chartInstance) return;

            chartInstance.data.datasets.forEach((ds, idx) => {
                const isVisible = this.selectedSites.includes(ds.label);
                chartInstance.setDatasetVisibility(idx, isVisible);
            });

            chartInstance.update();
        },

        applyFilter() {
            this.appliedStart = this.tempStart;
            this.appliedEnd = this.tempEnd;
            this.updateChartData();
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

            this.appliedStart = this.tempStart;
            this.appliedEnd = this.tempEnd;
            this.updateChartData();
        },

        buildChart() {
            const canvas = document.getElementById('siteChurnChart');
            if (!canvas) return;

            let filtered = this.rawData;

            if (this.appliedStart) {
                filtered = filtered.filter(d => d.Month >= this.appliedStart);
            }
            if (this.appliedEnd) {
                filtered = filtered.filter(d => d.Month <= this.appliedEnd);
            }

            const months = [...new Set(filtered.map(d => d.Month))].filter(Boolean).sort();
            const colors = ['#D98E2B', '#4A6C8C', '#3FA76B', '#C0453A', '#8A6D3B', '#6B8E9E', '#9B8557', '#5C7A99'];

            // Pre-load all available sites into the dataset array
            const datasets = this.availableSites.map((site, idx) => ({
                label: site,
                data: months.map(m => {
                    const row = filtered.find(d => d.Site === site && d.Month === m);
                    return row ? row['Monthly Churn Percentage'] : null;
                }),
                borderColor: colors[idx % colors.length],
                backgroundColor: colors[idx % colors.length],
                tension: 0.2,
                spanGaps: false
            }));

            // Instantiate Chart on local variable (bypasses Alpine Proxy)
            chartInstance = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: { labels: months, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                // Custom legend generator to remove strikethrough and dim unchecked items
                                generateLabels: (chart) => {
                                    const isDark = document.body.classList.contains('dark');
                                    const defaultColor = isDark ? '#A7ACB4' : '#565D66';
                                    const dimColor = isDark ? 'rgba(167, 172, 180, 0.35)' : 'rgba(86, 93, 102, 0.35)';

                                    return chart.data.datasets.map((dataset, i) => {
                                        const isVisible = chart.isDatasetVisible(i);

                                        return {
                                            text: dataset.label,
                                            datasetIndex: i,
                                            fillStyle: isVisible ? dataset.backgroundColor : 'transparent',
                                            strokeStyle: isVisible ? dataset.borderColor : dimColor,
                                            lineWidth: 2,
                                            // 1. Disable strikethrough
                                            textDecoration: 'none', 
                                            hidden: !isVisible,
                                            // 2. Dim the label text color when hidden
                                            fontColor: isVisible ? defaultColor : dimColor
                                        };
                                    });
                                }
                            },
                            // Maintain standard click-to-toggle functionality if clicked directly on the legend
                            onClick: (e, legendItem, legend) => {
                                const index = legendItem.datasetIndex;
                                const ci = legend.chart;
                                const isVisible = ci.isDatasetVisible(index);

                                ci.setDatasetVisibility(index, !isVisible);

                                // Sync Alpine state if site is toggled via chart legend directly
                                const siteName = ci.data.datasets[index].label;
                                const siteIdx = this.selectedSites.indexOf(siteName);
                                if (isVisible && siteIdx > -1) {
                                    this.selectedSites.splice(siteIdx, 1);
                                } else if (!isVisible && siteIdx === -1) {
                                    this.selectedSites.push(siteName);
                                }

                                ci.update();
                            }
                        }
                    },
                    scales: {
                        y: {
                            title: { display: true, text: 'Churn Percentage (%)' },
                            ticks: { callback: v => v + '%' }
                        }
                    }
                }
            });

            this.syncVisibilities();
        },

        updateTheme() {
            if (!chartInstance) return;

            const isDark = document.body.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.07)' : 'rgba(10, 12, 16, 0.08)';
            const textColor = isDark ? '#A7ACB4' : '#565D66';

            // Update scale colors
            if (chartInstance.options.scales.x) {
                chartInstance.options.scales.x.ticks.color = textColor;
            }

            if (chartInstance.options.scales.y) {
                chartInstance.options.scales.y.grid.color = gridColor;
                chartInstance.options.scales.y.ticks.color = textColor;
                if (chartInstance.options.scales.y.title) {
                    chartInstance.options.scales.y.title.color = textColor;
                }
            }

            // Re-render chart without animation stutter
            chartInstance.update();
        },

        updateChartData() {
            if (!chartInstance) return;

            let filtered = this.rawData;

            if (this.appliedStart) {
                filtered = filtered.filter(d => d.Month >= this.appliedStart);
            }
            if (this.appliedEnd) {
                filtered = filtered.filter(d => d.Month <= this.appliedEnd);
            }

            const months = [...new Set(filtered.map(d => d.Month))].filter(Boolean).sort();

            chartInstance.data.labels = months;
            chartInstance.data.datasets.forEach(ds => {
                ds.data = months.map(m => {
                    const row = filtered.find(d => d.Site === ds.label && d.Month === m);
                    return row ? row['Monthly Churn Percentage'] : null;
                });
            });

            chartInstance.update();
        }
    };
}
</script>