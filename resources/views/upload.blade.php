<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNET-Analytics</title>

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

    <!-- Uncomment this if you want to see error outputs -->
    <!-- @if(isset($output) && $output)
        <pre class="text-xs bg-graphite-900 text-bad-400 p-4 rounded-sm overflow-x-auto mb-6 font-mono">{{ $output }}</pre>
    @endif -->

    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between border-b border-graphite-200 dark:border-graphite-800 pb-6 gap-4">
        <div class="flex items-center justify-between w-full md:w-auto">
            <div>
                <a href="/" class="inline-flex items-center gap-1.5 font-mono text-xs uppercase tracking-wider text-graphite-500 hover:text-signal-600 dark:text-graphite-400 dark:hover:text-signal-400 mb-3 transition-colors">
                    <span>&larr;</span> Return to Portal
                </a>
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-good-500"></span>
                    <span class="op-eyebrow text-graphite-400 dark:text-graphite-500">Module CX&ndash;01</span>
                </div>
                <h1 class="font-display text-3xl font-semibold tracking-tight mt-1">VNET-Analytics</h1>
                <p class="text-graphite-500 dark:text-graphite-400 text-sm mt-1">Upload a standardized VNET data export (Excel / CSV) to generate customer analytics.</p>
            </div>

            <button @click="darkMode = !darkMode"
                    class="md:hidden p-2.5 rounded-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 text-graphite-500 dark:text-graphite-400 hover:border-signal-600 hover:text-signal-600 transition-colors">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
                <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.5 12H3M21 12h-1.5M5.6 5.6l1.06 1.06M17.34 17.34l1.06 1.06M5.6 18.4l1.06-1.06M17.34 6.66l1.06-1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
            </button>
        </div>

        <div class="flex items-center gap-3">
            <form action="/analyze" method="POST" enctype="multipart/form-data" @submit="isLoading = true"
                  class="flex-1 md:flex-none flex items-center gap-3 bg-paper-100 dark:bg-graphite-900 p-2 rounded-sm border border-graphite-200 dark:border-graphite-700 transition-colors">
                @csrf
                <input type="file" name="excel" accept=".xlsx,.xls,.csv" required
                       class="block w-full text-xs font-mono text-graphite-500 dark:text-graphite-400 file:mr-3 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-mono file:font-medium file:uppercase file:tracking-wider file:bg-graphite-100 file:text-graphite-700 dark:file:bg-graphite-800 dark:file:text-graphite-300 hover:file:bg-graphite-200 dark:hover:file:bg-graphite-700 cursor-pointer">
                <button type="submit" :disabled="isLoading" class="bg-signal-600 hover:bg-signal-700 dark:bg-signal-500 dark:hover:bg-signal-400 disabled:bg-graphite-400 disabled:cursor-not-allowed text-paper-50 dark:text-graphite-950 font-mono font-semibold uppercase tracking-wider px-5 py-2 rounded-sm transition-colors text-xs whitespace-nowrap flex items-center justify-center gap-2">
                    <span x-text="isLoading ? 'Analyzing…' : 'Analyze Data'"></span>
                </button>
            </form>

            <button @click="darkMode = !darkMode"
                    title="Toggle Display Mode"
                    class="hidden md:flex p-2.5 rounded-sm bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-700 text-graphite-500 dark:text-graphite-400 hover:border-signal-600 hover:text-signal-600 transition-colors items-center justify-center">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
                <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.5 12H3M21 12h-1.5M5.6 5.6l1.06 1.06M17.34 17.34l1.06 1.06M5.6 18.4l1.06-1.06M17.34 6.66l1.06-1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
            </button>
        </div>
    </div>

    @if(isset($images) && count($images) > 0)

    @php
        $img = $images->firstWhere('name', '1_mosaic');
        $imgTenure = $images->firstWhere('name', '2_tenure');
        $imgLoyalty = $images->firstWhere('name', '3_site_loyalty');
        $imgActive = $images->firstWhere('name', '4_active_customers');
        $imgChurn = $images->firstWhere('name', '5_churn_percentage');

        $imgRevenueAll = $images->firstWhere('name', '7_active_revenue_all');
        $imgRevenueMonthly = $images->firstWhere('name', '7_active_revenue_monthly');
        $imgRevenueWeekly = $images->firstWhere('name', '7_active_revenue_weekly');

        $imgDurationPrice = $images->firstWhere('name', '8_duration_vs_price');

        $weeklyChurnImages = $images->filter(fn($i) => str_contains($i['name'], '_weekly_churn'));
    @endphp

    @php
    $dashboard = $summaries['0_dashboard_cards'] ?? [];
@endphp

@if(!empty($dashboard))
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">

        <!-- Latest Dataset -->
        <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-graphite-400 dark:border-l-graphite-600 rounded-sm p-4 transition-colors">
            <p class="op-eyebrow text-graphite-400 dark:text-graphite-500">
                Latest Dataset
            </p>

            <h2 class="mt-2 font-mono text-xl font-semibold text-graphite-900 dark:text-graphite-100">
                {{ $summaries['0_dashboard_cards']['Dataset_Range'] }}
            </h2>

            <p class="mt-2 text-xs text-graphite-500 dark:text-graphite-400">
                Range spanning oldest to newest customer renewal in the dataset.
            </p>
        </div>

        <!-- Entries -->
        <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-graphite-400 dark:border-l-graphite-600 rounded-sm p-4 transition-colors">
            <p class="op-eyebrow text-graphite-400 dark:text-graphite-500">
                Dataset Entries
            </p>

            <h2 class="mt-2 font-mono text-xl font-semibold text-graphite-900 dark:text-graphite-100">
                {{ number_format($summaries['0_dashboard_cards']['Entry_Count']) }}
            </h2>

            <p class="mt-2 text-xs text-graphite-500 dark:text-graphite-400">
                Total records analyzed
            </p>
        </div>

        <!-- Regions -->
        <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-graphite-400 dark:border-l-graphite-600 rounded-sm p-4 transition-colors">
            <p class="op-eyebrow text-graphite-400 dark:text-graphite-500 mb-3">
                Customers by Region
            </p>

            @foreach($summaries['0_dashboard_cards']['Region_Counts'] as $region => $count)
                <div class="flex justify-between py-1 font-mono text-sm">
                    <span class="text-graphite-500 dark:text-graphite-400">{{ $region }}</span>
                    <span class="font-semibold text-graphite-900 dark:text-graphite-100">{{ number_format($count) }}</span>
                </div>
            @endforeach
        </div>

        <!-- Status -->
        <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 border-l-2 border-l-good-500 rounded-sm p-4 transition-colors">
            <p class="op-eyebrow text-graphite-400 dark:text-graphite-500 mb-3">
                Customer Status
            </p>

            <div class="space-y-1.5 font-mono text-sm">
            <div class="flex justify-between py-0.5">
                <span class="text-graphite-500 dark:text-graphite-400">Rows Used</span>
                <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                    {{ number_format($summaries['0_dashboard_cards']['Data_Quality']['Rows_Used']) }}
                </span>
            </div>

            <div class="flex justify-between py-0.5">
                <span class="text-graphite-500 dark:text-graphite-400">Total Rows</span>
                <span class="font-semibold text-graphite-900 dark:text-graphite-100">
                    {{ number_format($summaries['0_dashboard_cards']['Data_Quality']['Rows_Total']) }}
                </span>
            </div>

            <div class="flex justify-between py-1 border-t border-graphite-200 dark:border-graphite-700 pt-2">
                <span class="text-graphite-500 dark:text-graphite-400">Data Quality</span>
                <span class="font-bold text-good-600 dark:text-good-400">
                    {{ $summaries['0_dashboard_cards']['Data_Quality']['Score'] }}%
                </span>
            </div>
            </div>
        </div>

    </div>
    @endif


    <div class="space-y-6">
        <div class="flex flex-wrap gap-1 border-b border-graphite-200 dark:border-graphite-800">
            <button @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'"
                    class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Overview
            </button>

            <button @click="activeTab = 'tenure'"
                    :class="activeTab === 'tenure' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'"
                    class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Tenure &amp; Loyalty
            </button>

            <button @click="activeTab = 'activity'"
                    :class="activeTab === 'activity' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'"
                    class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Active &amp; Churn
            </button>

            <button @click="activeTab = 'weekly'"
                    :class="activeTab === 'weekly' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'"
                    class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Site Churn
            </button>

            <button @click="activeTab = 'revenue'"
                    :class="activeTab === 'revenue' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'"
                    class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Revenue
            </button>

            <button @click="activeTab = 'ltv'"
                    :class="activeTab === 'ltv' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'"
                    class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Lifetime Value
            </button>

            <button @click="activeTab = 'relationships'"
                    :class="activeTab === 'relationships' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'"
                    class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Relationships
            </button>
        </div>

        @include('analytics.sections.overview')
        @include('analytics.sections.tenure')
        @include('analytics.sections.activity')
        @include('analytics.sections.weekly')
        @include('analytics.sections.revenue')
        @include('analytics.sections.ltv')
        @include('analytics.sections.relationships')

        @include('analytics.partials.modal')
    </div>
    @else
    <div class="my-10">
        <div x-show="!isLoading" x-cloak class="bg-paper-100 dark:bg-graphite-900 rounded-sm border border-dashed border-graphite-300 dark:border-graphite-700 p-12 text-center transition-colors">
            <div class="inline-flex p-3.5 rounded-sm border border-graphite-300 dark:border-graphite-700 text-graphite-500 dark:text-graphite-400 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="font-mono text-sm font-semibold uppercase tracking-wider text-graphite-700 dark:text-graphite-300">No Dataset Loaded</h3>
            <p class="text-graphite-500 dark:text-graphite-400 text-sm max-w-md mx-auto mt-2">Upload a VNET data export using the control above to generate charts and statistical summaries.</p>
        </div>

        <div x-show="isLoading" x-cloak class="bg-paper-100 dark:bg-graphite-900 rounded-sm border border-dashed border-graphite-300 dark:border-graphite-700 p-16 text-center transition-colors">
            <div class="flex flex-col items-center justify-center gap-4">
                <svg class="h-9 w-9 animate-spin text-signal-600 dark:text-signal-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <h3 class="font-mono text-sm font-semibold uppercase tracking-wider text-graphite-700 dark:text-graphite-300">Processing Dataset&hellip;</h3>
                    <p class="text-graphite-500 dark:text-graphite-400 text-sm mt-2">This may take a few moments while charts are generated.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</body>
</html>