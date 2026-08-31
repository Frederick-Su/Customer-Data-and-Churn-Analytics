<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNET Ticketing Analytics</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        graphite: {
                            950: '#0A0C10', 900: '#14171B', 850: '#181C21', 800: '#1D2127',
                            700: '#272C33', 600: '#3A4048', 500: '#565D66', 400: '#7A828C',
                            300: '#A7ACB4', 200: '#D2D5D9', 100: '#E7E9EB',
                        },
                        paper: { 50: '#F3F4F0', 100: '#FFFFFF' },
                        signal: { 400: '#E8A854', 500: '#D98E2B', 600: '#B8721A', 700: '#96590F' },
                        good: { 400: '#5CBE85', 500: '#3FA76B', 600: '#2F8556' },
                        bad: { 400: '#DB6C60', 500: '#C0453A', 600: '#A23429' },
                    },
                    fontFamily: {
                        display: ['"Space Grotesk"', 'sans-serif'],
                        body: ['"IBM Plex Sans"', 'sans-serif'],
                        mono: ['"IBM Plex Mono"', 'monospace'],
                    },
                },
            },
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body {
            background-image: radial-gradient(rgba(122,130,140,0.18) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        ::selection { background: #D98E2B; color: #0A0C10; }
        .op-eyebrow { font-family: 'IBM Plex Mono', monospace; font-size: 11px; text-transform: uppercase; letter-spacing: 0.14em; }
    </style>
</head>
<body
    x-data="{
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
        activeTab: 'overview',
        modalOpen: false,
        modalImg: '',
        modalTitle: '',
        isLoading: false,
        openModal(url, title) {
            this.modalImg = url;
            this.modalTitle = title;
            this.modalOpen = true;
        }
    }"
    x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
    :class="{ 'dark bg-graphite-950 text-graphite-100': darkMode, 'bg-paper-50 text-graphite-900': !darkMode }"
    class="font-body antialiased min-h-screen transition-colors duration-200"
>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between border-b border-graphite-200 dark:border-graphite-800 pb-6 gap-4">
        <div class="flex items-center justify-between w-full md:w-auto">
            <div>
                <a href="/" class="inline-flex items-center gap-1.5 font-mono text-xs uppercase tracking-wider text-graphite-500 hover:text-signal-600 dark:text-graphite-400 dark:hover:text-signal-400 mb-3 transition-colors">
                    <span>&larr;</span> Return to Portal
                </a>
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-good-500"></span>
                    <span class="op-eyebrow text-graphite-400 dark:text-graphite-500">Module TK&ndash;01</span>
                </div>
                <h1 class="font-display text-3xl font-semibold tracking-tight mt-1">Ticketing Analytics</h1>
                <p class="text-graphite-500 dark:text-graphite-400 text-sm mt-1">Upload a ticketing export to generate agent performance and complaint metrics.</p>
            </div>

            <button @click="darkMode = !darkMode" class="md:hidden p-2.5 rounded-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 text-graphite-500 dark:text-graphite-400 hover:border-signal-600 hover:text-signal-600 transition-colors">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
                <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.5 12H3M21 12h-1.5M5.6 5.6l1.06 1.06M17.34 17.34l1.06 1.06M5.6 18.4l1.06-1.06M17.34 6.66l1.06-1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
            </button>
        </div>

        <div class="flex items-center gap-3">
            <form action="/analyze-tickets" method="POST" enctype="multipart/form-data" @submit="isLoading = true" class="flex-1 md:flex-none flex items-center gap-3 bg-paper-100 dark:bg-graphite-900 p-2 rounded-sm border border-graphite-200 dark:border-graphite-700 transition-colors">
                @csrf
                <input type="file" name="excel" accept=".xlsx,.xls,.csv" required class="block w-full text-xs font-mono text-graphite-500 dark:text-graphite-400 file:mr-3 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-mono file:font-medium file:uppercase file:tracking-wider file:bg-graphite-100 file:text-graphite-700 dark:file:bg-graphite-800 dark:file:text-graphite-300 hover:file:bg-graphite-200 dark:hover:file:bg-graphite-700 cursor-pointer">
                <button type="submit" :disabled="isLoading" class="bg-signal-600 hover:bg-signal-700 dark:bg-signal-500 dark:hover:bg-signal-400 disabled:bg-graphite-400 disabled:cursor-not-allowed text-paper-50 dark:text-graphite-950 font-mono font-semibold uppercase tracking-wider px-5 py-2 rounded-sm transition-colors text-xs whitespace-nowrap flex items-center justify-center gap-2">
                    <span x-text="isLoading ? 'Analyzing…' : 'Analyze Data'"></span>
                </button>
            </form>

            <button @click="darkMode = !darkMode" title="Toggle Display Mode" class="hidden md:flex p-2.5 rounded-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 text-graphite-500 dark:text-graphite-400 hover:border-signal-600 hover:text-signal-600 transition-colors items-center justify-center">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
                <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.5 12H3M21 12h-1.5M5.6 5.6l1.06 1.06M17.34 17.34l1.06 1.06M5.6 18.4l1.06-1.06M17.34 6.66l1.06-1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
            </button>
        </div>
    </div>

    @if(!empty($summaries) || !empty($images))
        @php
            $dashboard = $summaries['0_dashboard_cards'] ?? [];
            
            $imgDurationDist = !empty($images) ? $images->firstWhere('name', '6_duration_distribution') : null;
            $imgComplaintProp = !empty($images) ? $images->firstWhere('name', '7_complaint_proportion') : null;
            $imgHeatmap = !empty($images) ? $images->firstWhere('name', '9_complaint_heatmap') : null;
        @endphp

        <!-- Dashboard Cards -->
        @if(!empty($dashboard))
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-graphite-400 dark:border-l-graphite-600 rounded-sm p-4">
                <p class="op-eyebrow text-graphite-400 dark:text-graphite-500">Total Rows Evaluated</p>
                <h2 class="mt-2 font-mono text-2xl font-semibold text-graphite-900 dark:text-graphite-100">{{ number_format($dashboard['Total_Rows']) }}</h2>
            </div>
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-signal-600 dark:border-l-signal-500 rounded-sm p-4">
                <p class="op-eyebrow text-graphite-400 dark:text-graphite-500">Unique Tickets</p>
                <h2 class="mt-2 font-mono text-2xl font-semibold text-signal-600 dark:text-signal-400">{{ number_format($dashboard['Unique_Tickets']) }}</h2>
            </div>
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-bad-600 dark:border-l-bad-500 rounded-sm p-4">
                <p class="op-eyebrow text-graphite-400 dark:text-graphite-500">Duplicate Tickets</p>
                <h2 class="mt-2 font-mono text-2xl font-semibold text-bad-600 dark:text-bad-400">{{ number_format($dashboard['Duplicate_Tickets']) }}</h2>
            </div>
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-good-600 dark:border-l-good-500 rounded-sm p-4">
                <p class="op-eyebrow text-graphite-400 dark:text-graphite-500">Unique VN IDs</p>
                <h2 class="mt-2 font-mono text-2xl font-semibold text-good-600 dark:text-good-400">{{ number_format($dashboard['Unique_VN_IDs']) }}</h2>
            </div>
        </div>
        @endif

        <div class="space-y-6">
            <div class="flex flex-wrap gap-1 border-b border-graphite-200 dark:border-graphite-800">
                <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-signal-600 text-signal-600 font-semibold' : 'border-transparent text-graphite-500'" class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">Overview &amp; Volume</button>
                <button @click="activeTab = 'duration'" :class="activeTab === 'duration' ? 'border-signal-600 text-signal-600 font-semibold' : 'border-transparent text-graphite-500'" class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">Resolution &amp; Duration</button>
                <button @click="activeTab = 'hotspots'" :class="activeTab === 'hotspots' ? 'border-signal-600 text-signal-600 font-semibold' : 'border-transparent text-graphite-500'" class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">Hotspots &amp; Deep Dive</button>
            </div>

            <!-- Partials Included Here -->
            @include('tickets.sections.overview')
            @include('tickets.sections.duration')
            @include('tickets.sections.hotspots')

            <!-- Fallback Image Modal -->
            <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-graphite-950/85 p-4 backdrop-blur-sm" x-cloak>
                <div @click.away="modalOpen = false" class="bg-paper-100 dark:bg-graphite-900 rounded-sm shadow-2xl max-w-5xl w-full relative border border-graphite-200 dark:border-graphite-700">
                    <button @click="modalOpen = false" class="absolute -top-4 -right-4 bg-paper-100 dark:bg-graphite-800 text-graphite-500 hover:text-signal-600 rounded-sm p-2 shadow-lg border border-graphite-200 dark:border-graphite-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-700">
                        <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400" x-text="modalTitle"></h3>
                    </div>
                    <div class="p-4 overflow-auto max-h-[80vh]">
                        <img :src="modalImg" class="w-full rounded-sm">
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="my-10">
            <div x-show="!isLoading" x-cloak class="bg-paper-100 dark:bg-graphite-900 rounded-sm border border-dashed border-graphite-300 dark:border-graphite-700 p-12 text-center transition-colors">
                <div class="inline-flex p-3.5 rounded-sm border border-graphite-300 dark:border-graphite-700 text-graphite-500 dark:text-graphite-400 mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="font-mono text-sm font-semibold uppercase tracking-wider text-graphite-700 dark:text-graphite-300">No Dataset Loaded</h3>
                <p class="text-graphite-500 dark:text-graphite-400 text-sm max-w-md mx-auto mt-2">Upload a ticketing export using the control above to generate charts and summaries.</p>
            </div>

            <div x-show="isLoading" x-cloak class="bg-paper-100 dark:bg-graphite-900 rounded-sm border border-dashed border-graphite-300 dark:border-graphite-700 p-16 text-center transition-colors">
                <div class="flex flex-col items-center justify-center gap-4">
                    <svg class="h-9 w-9 animate-spin text-signal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <div>
                        <h3 class="font-mono text-sm font-semibold uppercase tracking-wider text-graphite-700 dark:text-graphite-300">Processing Dataset&hellip;</h3>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Reusable Alpine / Chart.js Factory -->
<script>
    // Shared styling logic for all charts
    function getChartTheme() {
        const isDark = document.body.classList.contains('dark');
        return {
            gridColor: isDark ? 'rgba(255,255,255,0.07)' : 'rgba(10,12,16,0.08)',
            textColor: isDark ? '#A7ACB4' : '#565D66',
            fontSettings: { family: "'IBM Plex Mono', monospace", size: 11 }
        };
    }

    function applyThemeToInstance(chartInstance, horizontal) {
        if (!chartInstance) return;
        const theme = getChartTheme();
        chartInstance.options.scales.x.grid.color = horizontal ? theme.gridColor : 'transparent';
        chartInstance.options.scales.x.ticks.color = theme.textColor;
        chartInstance.options.scales.y.grid.color = !horizontal ? theme.gridColor : 'transparent';
        chartInstance.options.scales.y.ticks.color = theme.textColor;
        chartInstance.update();
    }

    // 1. STANDARD CHART (No filters - used for complaints)
    function standardTicketChart(rawData, xKey, yKey, type = 'bar', horizontal = false) {
        let chartInstance = null;
        return {
            init() {
                if (!rawData || Object.keys(rawData).length === 0) return;
                this.$nextTick(() => this.buildChart());
                this.$watch('darkMode', () => applyThemeToInstance(chartInstance, horizontal));
            },
            buildChart() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;

                const labels = Array.isArray(rawData) ? rawData.map(d => d[xKey]) : Object.keys(rawData);
                const values = Array.isArray(rawData) ? rawData.map(d => d[yKey]) : Object.values(rawData);
                const theme = getChartTheme();

                chartInstance = new Chart(canvas.getContext('2d'), {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: '#D98E2B',
                            borderColor: '#D98E2B',
                            borderWidth: 1,
                            tension: 0.3,
                            fill: type === 'line' ? { target: 'origin', above: 'rgba(217, 142, 43, 0.15)' } : false,
                        }]
                    },
                    options: {
                        indexAxis: horizontal ? 'y' : 'x',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { color: horizontal ? theme.gridColor : 'transparent' }, ticks: { color: theme.textColor, font: theme.fontSettings } },
                            y: { grid: { color: !horizontal ? theme.gridColor : 'transparent' }, ticks: { color: theme.textColor, font: theme.fontSettings } }
                        }
                    }
                });
            }
        };
    }

    // 2. AREA FILTERED CHART (Checkboxes)
    function areaFilteredChart(rawData, xKey, yKey, type = 'bar', horizontal = false) {
        let chartInstance = null;
        return {
            rawData: rawData || [],
            availableAreas: [],
            selectedAreas: [],
            tempStart: '',
            tempEnd: '',

            init() {
                if (this.rawData.length === 0) return;
                
                // Extract unique area options using xKey
                this.availableAreas = [...new Set(this.rawData.map(d => d[xKey]))].filter(Boolean).sort();
                this.selectedAreas = [...this.availableAreas];

                this.resetDateFilter();
                this.$nextTick(() => this.buildChart());
                this.$watch('darkMode', () => applyThemeToInstance(chartInstance, horizontal));
                this.$watch('selectedAreas', () => this.updateChart());
            },

            toggleArea(area) {
                const idx = this.selectedAreas.indexOf(area);
                if (idx > -1) this.selectedAreas.splice(idx, 1);
                else this.selectedAreas.push(area);
            },
            selectAll() { this.selectedAreas = [...this.availableAreas]; },
            clearAll() { this.selectedAreas = []; },

            applyDateFilter() {
                this.updateChart();
            },

            resetDateFilter() {
                const dates = this.rawData.map(d => d.Date).filter(Boolean).sort();
                if (dates.length > 0) {
                    this.tempStart = dates[0];
                    this.tempEnd = dates[dates.length - 1];
                }
                this.updateChart();
            },

            getAggregatedData() {
                // Filter by selected areas AND optional date range
                const filtered = this.rawData.filter(d => {
                    const areaMatch = this.selectedAreas.includes(d[xKey]);
                    const startMatch = !this.tempStart || !d.Date || d.Date >= this.tempStart;
                    const endMatch = !this.tempEnd || !d.Date || d.Date <= this.tempEnd;
                    return areaMatch && startMatch && endMatch;
                });

                // IF yKey is provided, use pre-calculated values; OTHERWISE count raw record occurrences
                const results = {};
                filtered.forEach(d => {
                    const label = d[xKey];
                    if (yKey) {
                        results[label] = d[yKey];
                    } else {
                        results[label] = (results[label] || 0) + 1;
                    }
                });

                // Sort descending by value
                const sorted = Object.entries(results).sort((a, b) => b[1] - a[1]);
                return {
                    labels: sorted.map(item => item[0]),
                    values: sorted.map(item => item[1])
                };
            },

            buildChart() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;

                const agg = this.getAggregatedData();
                const theme = getChartTheme();

                chartInstance = new Chart(canvas.getContext('2d'), {
                    type: type,
                    data: {
                        labels: agg.labels,
                        datasets: [{
                            data: agg.values,
                            backgroundColor: '#D98E2B', 
                            borderColor: '#D98E2B', 
                            borderWidth: 1, 
                            tension: 0.3
                        }]
                    },
                    options: {
                        indexAxis: horizontal ? 'y' : 'x',
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { color: horizontal ? theme.gridColor : 'transparent' }, ticks: { color: theme.textColor, font: theme.fontSettings } },
                            y: { grid: { color: !horizontal ? theme.gridColor : 'transparent' }, ticks: { color: theme.textColor, font: theme.fontSettings } }
                        }
                    }
                });
            },

            updateChart() {
                if (!chartInstance) return;
                const agg = this.getAggregatedData();
                chartInstance.data.labels = agg.labels;
                chartInstance.data.datasets[0].data = agg.values;
                chartInstance.update();
            }
        };
    }

    // 3. DATE FILTERED CHART (Month Inputs)
    function dateFilteredChart(rawData, xKey, yKey, type = 'line', horizontal = false) {
        let chartInstance = null;
        return {
            rawData: rawData || [],
            tempStart: '',
            tempEnd: '',

            init() {
                if (this.rawData.length === 0) return;
                this.resetFilter();
                this.$nextTick(() => this.buildChart());
                this.$watch('darkMode', () => applyThemeToInstance(chartInstance, horizontal));
            },

            applyFilter() { this.updateChart(); },
            
            resetFilter() {
                const dates = this.rawData.map(d => d[xKey]).sort();
                if (dates.length > 0) {
                    this.tempStart = dates[0];                  // Earliest date in the dataset
                    this.tempEnd = dates[dates.length - 1];     // Latest date in the dataset
                }
                this.updateChart();
            },

            getFilteredData() {
                let filtered = this.rawData;
                if (this.tempStart) filtered = filtered.filter(d => d[xKey] >= this.tempStart);
                if (this.tempEnd) filtered = filtered.filter(d => d[xKey] <= this.tempEnd);
                return filtered;
            },

            buildChart() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;

                const filtered = this.getFilteredData();
                const theme = getChartTheme();

                chartInstance = new Chart(canvas.getContext('2d'), {
                    type: type,
                    data: {
                        labels: filtered.map(d => d[xKey]),
                        datasets: [{
                            data: filtered.map(d => d[yKey]),
                            backgroundColor: '#D98E2B', borderColor: '#D98E2B', borderWidth: 1, tension: 0.3,
                            fill: type === 'line' ? { target: 'origin', above: 'rgba(217, 142, 43, 0.15)' } : false,
                        }]
                    },
                    options: {
                        indexAxis: horizontal ? 'y' : 'x',
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { color: horizontal ? theme.gridColor : 'transparent' }, ticks: { color: theme.textColor, font: theme.fontSettings } },
                            y: { grid: { color: !horizontal ? theme.gridColor : 'transparent' }, ticks: { color: theme.textColor, font: theme.fontSettings } }
                        }
                    }
                });
            },

            updateChart() {
                if (!chartInstance) return;
                const filtered = this.getFilteredData();
                chartInstance.data.labels = filtered.map(d => d[xKey]);
                chartInstance.data.datasets[0].data = filtered.map(d => d[yKey]);
                chartInstance.update();
            }
        };
    }

    // 4. PROPORTION PIE CHART
    function complaintPieChart(rawData) {
        let chartInstance = null;
        return {
            rawData: rawData || [],
            tempStart: '',
            tempEnd: '',
            colors: ['#A6CEE3', '#1F78B4', '#B2DF8A', '#33A02C', '#FB9A99', '#E31A1C', '#FDBF6F', '#FF7F00', '#CAB2D6', '#6A3D9A'],

            init() {
                if (!this.rawData.length) return;
                this.resetFilter();
                this.$nextTick(() => this.buildChart());
                this.$watch('darkMode', () => this.updateChart());
            },

            applyFilter() {
                this.updateChart();
            },

            resetFilter() {
                // Find minimum and maximum "YYYY-MM" values from rawData
                const dates = this.rawData
                    .map(d => d.Date)
                    .filter(Boolean)
                    .sort();

                if (dates.length > 0) {
                    this.tempStart = dates[0];
                    this.tempEnd = dates[dates.length - 1];
                }
                this.updateChart();
            },

            getFilteredData() {
                return this.rawData.filter(d => {
                    if (!d.Date) return false;
                    // Standard YYYY-MM string comparison works lexicographically
                    const afterStart = !this.tempStart || d.Date >= this.tempStart;
                    const beforeEnd = !this.tempEnd || d.Date <= this.tempEnd;
                    return afterStart && beforeEnd;
                });
            },

            processSlices(data) {
                const counts = {};
                data.forEach(d => { 
                    counts[d['Type Complaint']] = (counts[d['Type Complaint']] || 0) + 1; 
                });

                const total = Object.values(counts).reduce((a, b) => a + b, 0);
                if (!total) return { labels: [], values: [] };

                const labels = [];
                const values = [];
                let otherCount = 0;

                // Group slices < 1% into "Other"
                for (const [key, count] of Object.entries(counts)) {
                    if (count / total < 0.01) {
                        otherCount += count;
                    } else {
                        labels.push(key);
                        values.push(count);
                    }
                }

                if (otherCount > 0) {
                    labels.push('Other');
                    values.push(otherCount);
                }

                return { labels, values };
            },

            buildChart() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;

                const sliceData = this.processSlices(this.getFilteredData());
                const isDark = document.body.classList.contains('dark');

                chartInstance = new Chart(canvas.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: sliceData.labels,
                        datasets: [{
                            data: sliceData.values,
                            backgroundColor: this.colors,
                            borderColor: isDark ? '#14171B' : '#FFFFFF',
                            borderWidth: 1.5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    color: isDark ? '#A7ACB4' : '#565D66',
                                    font: { family: "'IBM Plex Mono', monospace", size: 11 }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                        const pct = ((ctx.raw / sum) * 100).toFixed(1);
                                        return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            },

            updateChart() {
                if (!chartInstance) return;
                const sliceData = this.processSlices(this.getFilteredData());
                const isDark = document.body.classList.contains('dark');

                chartInstance.data.labels = sliceData.labels;
                chartInstance.data.datasets[0].data = sliceData.values;
                chartInstance.data.datasets[0].borderColor = isDark ? '#14171B' : '#FFFFFF';
                chartInstance.options.plugins.legend.labels.color = isDark ? '#A7ACB4' : '#565D66';
                chartInstance.update();
            }
        };
    }

    // 5. MOST FREQUENT COMPLAINERS CHART
    function vnIdHotspotChart(rawData) {
        let chartInstance = null;
        return {
            rawData: rawData || [],
            tempStart: '',
            tempEnd: '',

            init() {
                if (this.rawData.length === 0) return;

                this.resetDateFilter();
                this.$nextTick(() => this.buildChart());
                this.$watch('darkMode', () => applyThemeToInstance(chartInstance, true));
            },

            // Helper to compute previous month given YYYY-MM
            getPreviousMonth(yearMonthStr) {
                const [yearStr, monthStr] = yearMonthStr.split('-');
                let year = parseInt(yearStr, 10);
                let month = parseInt(monthStr, 10) - 1; // Subtract 1 month

                if (month === 0) {
                    month = 12;
                    year -= 1;
                }

                return `${year}-${String(month).padStart(2, '0')}`;
            },

            resetDateFilter() {
                // Find unique YYYY-MM dates from dataset
                const dates = this.rawData
                    .map(d => d.Date || d.Month)
                    .filter(Boolean)
                    .sort();

                if (dates.length > 0) {
                    const latestDate = dates[dates.length - 1];
                    this.tempStart = this.getPreviousMonth(latestDate); // Defaults to previous month
                    this.tempEnd = latestDate;                          // Latest month in dataset
                }
                this.updateChart();
            },

            getFilteredData() {
                let filtered = this.rawData;
                
                // Apply month range filtering if dates exist in records
                if (this.tempStart || this.tempEnd) {
                    filtered = filtered.filter(d => {
                        const rowDate = d.Date || d.Month;
                        if (!rowDate) return true;
                        const afterStart = !this.tempStart || rowDate >= this.tempStart;
                        const beforeEnd = !this.tempEnd || rowDate <= this.tempEnd;
                        return afterStart && beforeEnd;
                    });
                }

                // Aggregate counts by VN ID / User ID
                const counts = {};
                filtered.forEach(d => {
                    const id = d['VN_ID'] || d['VN ID'] || d['User ID'] || d.label;
                    if (id) {
                        counts[id] = (counts[id] || 0) + (d.Value || d.count || 1);
                    }
                });

                // Sort descending and take top 20
                const sorted = Object.entries(counts)
                    .sort((a, b) => b[1] - a[1])
                    .slice(0, 20);

                return {
                    labels: sorted.map(i => i[0]),
                    values: sorted.map(i => i[1])
                };
            },

            applyDateFilter() {
                this.updateChart();
            },

            buildChart() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;

                const agg = this.getFilteredData();
                const theme = getChartTheme();

                chartInstance = new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: agg.labels,
                        datasets: [{
                            data: agg.values,
                            backgroundColor: '#D98E2B',
                            borderColor: '#D98E2B',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        indexAxis: 'y', // Horizontal Bar Chart
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { color: theme.gridColor }, ticks: { color: theme.textColor, font: theme.fontSettings } },
                            y: { grid: { color: 'transparent' }, ticks: { color: theme.textColor, font: theme.fontSettings } }
                        }
                    }
                });
            },

            updateChart() {
                if (!chartInstance) return;
                const agg = this.getFilteredData();
                chartInstance.data.labels = agg.labels;
                chartInstance.data.datasets[0].data = agg.values;
                chartInstance.update();
            }
        };
    }
</script>
</body>
</html>