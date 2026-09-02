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
                            <input type="checkbox" 
                                :value="region" 
                                :checked="selectedRegions.includes(region)"
                                @change="toggleRegion(region)"
                                class="rounded-sm border-graphite-300 text-signal-600 focus:ring-signal-500">
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
                    Apply
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
    let activeChartInstance = null;
    let churnChartInstance = null;

    // Master arrays holding all calculated datasets in memory
    let masterActiveDatasets = [];
    let masterChurnDatasets = [];

    return {
        rawDataActive: rawActive || [],
        rawDataChurn: rawChurn || [],
        availableRegions: [],
        selectedRegions: [],
        tempStart: '',
        tempEnd: '',
        appliedStart: '',
        appliedEnd: '',

        initCharts() {
            const activeRegions = this.rawDataActive.map(d => d.Region);
            const churnRegions = this.rawDataChurn.map(d => d.Region);
            this.availableRegions = [...new Set([...activeRegions, ...churnRegions])].filter(Boolean).sort();
            this.selectedRegions = [...this.availableRegions];

            this.buildCharts();
        },

        toggleRegion(region) {
            const idx = this.selectedRegions.indexOf(region);
            if (idx > -1) {
                this.selectedRegions.splice(idx, 1);
            } else {
                this.selectedRegions.push(region);
            }
            this.updateDisplayedDatasets();
        },

        selectAllRegions() {
            this.selectedRegions = [...this.availableRegions];
            this.updateDisplayedDatasets();
        },

        deselectAllRegions() {
            this.selectedRegions = [];
            this.updateDisplayedDatasets();
        },

        // Filters master dataset arrays and updates charts directly
        updateDisplayedDatasets() {
            if (activeChartInstance) {
                activeChartInstance.data.datasets = masterActiveDatasets.filter(ds => 
                    this.selectedRegions.includes(ds.label)
                );
                activeChartInstance.update();
            }

            if (churnChartInstance) {
                churnChartInstance.data.datasets = masterChurnDatasets.filter(ds => 
                    this.selectedRegions.includes(ds.label)
                );
                churnChartInstance.update();
            }
        },

        applyFilter() {
            this.appliedStart = this.tempStart;
            this.appliedEnd = this.tempEnd;
            this.updateChartData();
        },

        resetFilter() {
            this.tempStart = '';
            this.tempEnd = '';
            this.appliedStart = '';
            this.appliedEnd = '';
            this.updateChartData();
        },

        buildCharts() {
            const activeCanvas = document.getElementById('activeCustomersChart');
            const churnCanvas = document.getElementById('churnPercentageChart');
            if (!activeCanvas || !churnCanvas) return;

            let activeData = this.rawDataActive;
            let churnData = this.rawDataChurn;

            if (this.appliedStart) {
                activeData = activeData.filter(d => d.Month >= this.appliedStart);
                churnData = churnData.filter(d => d.Month >= this.appliedStart);
            }
            if (this.appliedEnd) {
                activeData = activeData.filter(d => d.Month <= this.appliedEnd);
                churnData = churnData.filter(d => d.Month <= this.appliedEnd);
            }

            const activeMonths = [...new Set(activeData.map(d => d.Month))].filter(Boolean).sort();
            const churnMonths = [...new Set(churnData.map(d => d.Month))].filter(Boolean).sort();
            const colors = ['#D98E2B', '#4A6C8C', '#3FA76B', '#C0453A', '#8A6D3B', '#6B8E9E', '#9B8557'];

            // Store full dataset arrays in closure variables
            masterActiveDatasets = this.availableRegions.map((region, idx) => ({
                label: region,
                data: activeMonths.map(m => {
                    const row = activeData.find(d => d.Region === region && d.Month === m);
                    return row ? row['Active Customers'] : 0;
                }),
                borderColor: colors[idx % colors.length],
                backgroundColor: colors[idx % colors.length],
                tension: 0.3
            }));

            masterChurnDatasets = this.availableRegions.map((region, idx) => ({
                label: region,
                data: churnMonths.map(m => {
                    const row = churnData.find(d => d.Region === region && d.Month === m);
                    return row ? row['Monthly Churn Percentage'] : 0;
                }),
                borderColor: colors[idx % colors.length],
                backgroundColor: colors[idx % colors.length],
                tension: 0.3
            }));

            // Instantiate charts with filtered datasets matching current checkbox state
            activeChartInstance = new Chart(activeCanvas.getContext('2d'), {
                type: 'line',
                data: { 
                    labels: activeMonths, 
                    datasets: masterActiveDatasets.filter(ds => this.selectedRegions.includes(ds.label)) 
                },
                options: { responsive: true, maintainAspectRatio: false, }
            });

            churnChartInstance = new Chart(churnCanvas.getContext('2d'), {
                type: 'line',
                data: { 
                    labels: churnMonths, 
                    datasets: masterChurnDatasets.filter(ds => this.selectedRegions.includes(ds.label)) 
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    scales: { y: { ticks: { callback: v => v + '%' } } }
                }
            });
        },

        // Recalculates master numerical data when Apply (date ranges) is clicked
        updateChartData() {
            let activeData = this.rawDataActive;
            let churnData = this.rawDataChurn;

            if (this.appliedStart) {
                activeData = activeData.filter(d => d.Month >= this.appliedStart);
                churnData = churnData.filter(d => d.Month >= this.appliedStart);
            }
            if (this.appliedEnd) {
                activeData = activeData.filter(d => d.Month <= this.appliedEnd);
                churnData = churnData.filter(d => d.Month <= this.appliedEnd);
            }

            const activeMonths = [...new Set(activeData.map(d => d.Month))].filter(Boolean).sort();
            const churnMonths = [...new Set(churnData.map(d => d.Month))].filter(Boolean).sort();

            // Rebuild master values
            masterActiveDatasets.forEach(ds => {
                ds.data = activeMonths.map(m => {
                    const row = activeData.find(d => d.Region === ds.label && d.Month === m);
                    return row ? row['Active Customers'] : 0;
                });
            });

            masterChurnDatasets.forEach(ds => {
                ds.data = churnMonths.map(m => {
                    const row = churnData.find(d => d.Region === ds.label && d.Month === m);
                    return row ? row['Monthly Churn Percentage'] : 0;
                });
            });

            if (activeChartInstance) {
                activeChartInstance.data.labels = activeMonths;
            }
            if (churnChartInstance) {
                churnChartInstance.data.labels = churnMonths;
            }

            // Sync currently visible datasets to match updated numbers
            this.updateDisplayedDatasets();
        }
    };
}
</script>