<div x-show="activeTab === 'weekly'" 
     x-cloak 
     x-data="siteChurnFilter(@js($summaries['6_site_monthly_churn'] ?? []), @js($summaries['6b_same_month_cancellations'] ?? []))"
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
            <h4 class="font-mono text-[11px] uppercase tracking-wider text-graphite-500 dark:text-graphite-400 mb-3">
                % of Active Customers at Start of Month that Churn
            </h4>
            <canvas id="siteChurnChart" class="max-h-[450px] w-full"></canvas>
        </div>
        <div class="rounded-sm bg-graphite-50 dark:bg-graphite-950/40 border border-graphite-200 dark:border-graphite-800 p-4 mt-6">
            <h4 class="font-mono text-[11px] uppercase tracking-wider text-graphite-500 dark:text-graphite-400 mb-3">
                Same Month Cancellation Rate (% of New Signups)
            </h4>
            <canvas id="sameMonthCancellationChart" class="max-h-[350px] w-full"></canvas>
        </div>
        </div>
    </div>
</div>

<script>
function siteChurnFilter(siteData, sameMonthData) {
    // Local closure variables — unproxied by Alpine
    let chartInstance = null;
    let chartInstanceB = null;

    return {
        rawData: siteData || [],
        rawSameMonthData: sameMonthData || [], // Store new dataset
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

            this.regions = [...new Set(this.rawData.map(d => d.Region))].filter(Boolean).sort();
            this.selectedRegions = [...this.regions];

            this.availableSites = [...new Set(this.rawData.map(d => d.Site))].filter(Boolean).sort();
            this.selectedSites = [...this.availableSites];

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
                this.selectedRegions.splice(idx, 1);
                const regionSites = this.rawData.filter(d => d.Region === region).map(d => d.Site);
                this.selectedSites = this.selectedSites.filter(s => !regionSites.includes(s));
            } else {
                this.selectedRegions.push(region);
                const regionSites = this.rawData.filter(d => d.Region === region).map(d => d.Site);
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

        updateSingleVisibility(siteName, isVisible) {
            [chartInstance, chartInstanceB].forEach(ci => {
                if (!ci) return;
                const datasetIndex = ci.data.datasets.findIndex(ds => ds.label === siteName);
                if (datasetIndex !== -1) {
                    ci.setDatasetVisibility(datasetIndex, isVisible);
                    ci.update();
                }
            });
        },

        syncVisibilities() {
            [chartInstance, chartInstanceB].forEach(ci => {
                if (!ci) return;
                ci.data.datasets.forEach((ds, idx) => {
                    const isVisible = this.selectedSites.includes(ds.label);
                    ci.setDatasetVisibility(idx, isVisible);
                });
                ci.update();
            });
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
            const canvasA = document.getElementById('siteChurnChart');
            const canvasB = document.getElementById('sameMonthCancellationChart');
            if (!canvasA || !canvasB) return;

            let filteredA = this.rawData;
            let filteredB = this.rawSameMonthData;

            if (this.appliedStart) {
                filteredA = filteredA.filter(d => d.Month >= this.appliedStart);
                filteredB = filteredB.filter(d => d.Month >= this.appliedStart);
            }
            if (this.appliedEnd) {
                filteredA = filteredA.filter(d => d.Month <= this.appliedEnd);
                filteredB = filteredB.filter(d => d.Month <= this.appliedEnd);
            }

            const months = [...new Set(filteredA.map(d => d.Month))].filter(Boolean).sort();
            const colors = ['#D98E2B', '#4A6C8C', '#3FA76B', '#C0453A', '#8A6D3B', '#6B8E9E', '#9B8557', '#5C7A99'];

            // Build Datasets for Chart A (Main Monthly Churn Rate)
            const datasetsA = this.availableSites.map((site, idx) => ({
                label: site,
                data: months.map(m => {
                    const row = filteredA.find(d => d.Site === site && d.Month === m);
                    return {
                        x: m,
                        y: row ? row['Monthly Churn Percentage'] : null,
                        churned: row ? (row['Churned'] ?? row['Churned Customers'] ?? 0) : 0,
                        active: row ? (row['Active'] ?? row['Active Customers'] ?? 0) : 0
                    };
                }),
                borderColor: colors[idx % colors.length],
                backgroundColor: colors[idx % colors.length],
                tension: 0.2,
                spanGaps: false
            }));

            // Build Datasets for Chart B (Same-Month Cancellation Rate)
            const datasetsB = this.availableSites.map((site, idx) => ({
                label: site,
                data: months.map(m => {
                    const row = filteredB.find(d => d.Site === site && d.Month === m);
                    return {
                        x: m,
                        y: row ? row['Same Month Cancellation Rate'] : null,
                        cancellations: row ? (row['Same Month Cancellations'] ?? 0) : 0,
                        signups: row ? (row['New Signups'] ?? 0) : 0
                    };
                }),
                borderColor: colors[idx % colors.length],
                backgroundColor: colors[idx % colors.length],
                tension: 0.2,
                spanGaps: false
            }));

            // Instantiate Chart A
            chartInstance = new Chart(canvasA.getContext('2d'), {
                type: 'line',
                data: { labels: months, datasets: datasetsA },
                options: this.getCommonOptions('Churn Percentage (%)')
            });

            // Instantiate Chart B (Bottom Chart)
            chartInstanceB = new Chart(canvasB.getContext('2d'), {
                type: 'line',
                data: { labels: months, datasets: datasetsB },
                options: this.getCommonOptions('Cancellation Rate (%)')
            });

            this.syncVisibilities();
        },

        getCommonOptions(yAxisLabel) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const raw = context.raw;
                                const label = context.dataset.label || '';
                                
                                if (!raw || raw.y === null || raw.y === undefined) {
                                    return `${label}: N/A`;
                                }

                                const rate = Number(raw.y).toFixed(2);

                                // Chart A extra counts
                                if (raw.churned !== undefined && raw.active !== undefined) {
                                    return `${label}: ${rate}% (${raw.churned} churned / ${raw.active} active)`;
                                }

                                // Chart B extra counts
                                if (raw.cancellations !== undefined && raw.signups !== undefined) {
                                    return `${label}: ${rate}% (${raw.cancellations} cancels / ${raw.signups} signups)`;
                                }

                                return `${label}: ${rate}%`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        labels: {
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
                                        textDecoration: 'none',
                                        hidden: !isVisible,
                                        fontColor: isVisible ? defaultColor : dimColor
                                    };
                                });
                            }
                        },
                        onClick: (e, legendItem, legend) => {
                            const index = legendItem.datasetIndex;
                            const siteName = legend.chart.data.datasets[index].label;
                            const siteIdx = this.selectedSites.indexOf(siteName);
                            const isVisible = legend.chart.isDatasetVisible(index);

                            if (isVisible && siteIdx > -1) {
                                this.selectedSites.splice(siteIdx, 1);
                            } else if (!isVisible && siteIdx === -1) {
                                this.selectedSites.push(siteName);
                            }

                            this.syncVisibilities();
                        }
                    }
                },
                scales: {
                    y: {
                        title: { display: true, text: yAxisLabel },
                        ticks: { callback: v => v + '%' }
                    }
                }
            };
        },

        updateTheme() {
            [chartInstance, chartInstanceB].forEach(ci => {
                if (!ci) return;
                const isDark = document.body.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.07)' : 'rgba(10, 12, 16, 0.08)';
                const textColor = isDark ? '#A7ACB4' : '#565D66';

                if (ci.options.scales.x) ci.options.scales.x.ticks.color = textColor;
                if (ci.options.scales.y) {
                    ci.options.scales.y.grid.color = gridColor;
                    ci.options.scales.y.ticks.color = textColor;
                    if (ci.options.scales.y.title) ci.options.scales.y.title.color = textColor;
                }
                ci.update();
            });
        },

        updateChartData() {
            let filteredA = this.rawData;
            let filteredB = this.rawSameMonthData;

            if (this.appliedStart) {
                filteredA = filteredA.filter(d => d.Month >= this.appliedStart);
                filteredB = filteredB.filter(d => d.Month >= this.appliedStart);
            }
            if (this.appliedEnd) {
                filteredA = filteredA.filter(d => d.Month <= this.appliedEnd);
                filteredB = filteredB.filter(d => d.Month <= this.appliedEnd);
            }

            const months = [...new Set(filteredA.map(d => d.Month))].filter(Boolean).sort();

            if (chartInstance) {
                chartInstance.data.labels = months;
                chartInstance.data.datasets.forEach(ds => {
                    ds.data = months.map(m => {
                        const row = filteredA.find(d => d.Site === ds.label && d.Month === m);
                        return {
                            x: m,
                            y: row ? row['Monthly Churn Percentage'] : null,
                            churned: row ? (row['Churned'] ?? row['Churned Customers'] ?? 0) : 0,
                            active: row ? (row['Active'] ?? row['Active Customers'] ?? 0) : 0
                        };
                    });
                });
                chartInstance.update();
            }

            if (chartInstanceB) {
                chartInstanceB.data.labels = months;
                chartInstanceB.data.datasets.forEach(ds => {
                    ds.data = months.map(m => {
                        const row = filteredB.find(d => d.Site === ds.label && d.Month === m);
                        return {
                            x: m,
                            y: row ? row['Same Month Cancellation Rate'] : null,
                            cancellations: row ? (row['Same Month Cancellations'] ?? 0) : 0,
                            signups: row ? (row['New Signups'] ?? 0) : 0
                        };
                    });
                });
                chartInstanceB.update();
            }
        }
    };
}
</script>