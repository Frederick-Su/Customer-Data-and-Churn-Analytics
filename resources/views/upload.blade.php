<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNET-Analytics</title>

    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@700,500,400&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="max-w-screen-2xl mx-auto px-8 lg:px-12 py-10">
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
    :class="{ 'dark bg-slate-900 text-slate-100': darkMode, 'bg-slate-50 text-slate-800': !darkMode }"
    class="font-['Satoshi',sans-serif] antialiased min-h-screen transition-colors duration-200"

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Uncomment this if you want to see error outputs -->
    <!-- @if(isset($output) && $output)
        <pre class="text-xs bg-slate-900 text-red-300 p-4 rounded-lg overflow-x-auto mb-6">{{ $output }}</pre>
    @endif -->

    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 dark:border-slate-800 pb-6 gap-4">
        <div class="flex items-center justify-between w-full md:w-auto">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">VNET-Analytics</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Upload an standardized VNET data format Excel or CSV file to generate analytics and visualizations.</p>
            </div>

            <button @click="darkMode = !darkMode"
                    class="md:hidden p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-sm">
                <span x-show="!darkMode" class="text-lg">🌙</span>
                <span x-show="darkMode" x-cloak class="text-lg">☀️</span>
            </button>
        </div>

        <div class="flex items-center gap-3">
            <form action="/analyze" method="POST" enctype="multipart/form-data" @submit="isLoading = true"
                  class="flex-1 md:flex-none flex items-center gap-3 bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                @csrf
                <input type="file" name="excel" accept=".xlsx,.xls,.csv" required
                       class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/60 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 cursor-pointer">
                <button type="submit" :disabled="isLoading" class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 disabled:cursor-not-allowed text-white font-medium px-5 py-2 rounded-lg transition-colors text-sm shadow-sm whitespace-nowrap flex items-center justify-center gap-2">
                    <span x-text="isLoading ? 'Analyzing...' : 'Analyze Data'"></span>
                </button>
            </form>

            <button @click="darkMode = !darkMode"
                    title="Toggle Dark Mode"
                    class="hidden md:flex p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-sm items-center justify-center">
                <span x-show="!darkMode" class="text-lg">⏾</span>
                <span x-show="darkMode" x-cloak class="text-lg">𖤓</span>
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
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">

        <!-- Latest Dataset -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm transition-colors">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                Latest Dataset
            </p>

            <h2 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">
                {{ $summaries['0_dashboard_cards']['Dataset_Range'] }}
            </h2>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Range of data taken from oldest and newest customer renewals in the dataset.
            </p>
        </div>

        <!-- Entries -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm transition-colors">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                Dataset Entries
            </p>

            <h2 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">
                {{ number_format($summaries['0_dashboard_cards']['Entry_Count']) }}
            </h2>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Total records analyzed
            </p>
        </div>

        <!-- Regions -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm transition-colors">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-3">
                Customers by Region
            </p>

            @foreach($summaries['0_dashboard_cards']['Region_Counts'] as $region => $count)
                <div class="flex justify-between py-1">
                    <span class="text-slate-600 dark:text-slate-300">{{ $region }}</span>
                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ number_format($count) }}</span>
                </div>
            @endforeach
        </div>

        <!-- Status -->
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm transition-colors">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-3">
                Customer Status
            </p>

            <div class="space-y-2">
            <div class="flex justify-between py-1">
                <span class="text-slate-600 dark:text-slate-300">Rows Used</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100">
                    {{ number_format($summaries['0_dashboard_cards']['Data_Quality']['Rows_Used']) }}
                </span>
            </div>

            <div class="flex justify-between py-1">
                <span class="text-slate-600 dark:text-slate-300">Total Rows</span>
                <span class="font-semibold text-slate-900 dark:text-slate-100">
                    {{ number_format($summaries['0_dashboard_cards']['Data_Quality']['Rows_Total']) }}
                </span>
            </div>

            <div class="flex justify-between py-1 border-t border-slate-200 dark:border-slate-700 pt-2">
                <span class="text-slate-600 dark:text-slate-300">Data Quality</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">
                    {{ $summaries['0_dashboard_cards']['Data_Quality']['Score'] }}%
                </span>
            </div>
            </div>
        </div>

    </div>
    @endif
    

    
    <div class="space-y-6">
        <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800 pb-1">
            <button @click="activeTab = 'overview'"
                    :class="activeTab === 'overview' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Overview & Demographics
            </button>

            <button @click="activeTab = 'tenure'"
                    :class="activeTab === 'tenure' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Tenure & Loyalty
            </button>

            <button @click="activeTab = 'activity'"
                    :class="activeTab === 'activity' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Active & Churn Trends
            </button>

            <button @click="activeTab = 'weekly'"
                    :class="activeTab === 'weekly' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Site Monthly Churn
            </button>

            <button @click="activeTab = 'revenue'"
                    :class="activeTab === 'revenue' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Active Revenue
            </button>

            <button @click="activeTab = 'relationships'"
                    :class="activeTab === 'relationships' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'"
                    class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Customer Relationships
            </button>
        </div>

        @include('analytics.sections.overview')
        @include('analytics.sections.tenure')
        @include('analytics.sections.activity')
        @include('analytics.sections.weekly')
        @include('analytics.sections.revenue')
        @include('analytics.sections.relationships')

        @include('analytics.partials.modal')
    </div>
    @else
    <div class="my-10">
        <div x-show="!isLoading" x-cloak class="bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center transition-colors">
            <div class="inline-flex p-4 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">No Analytics Loaded</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto mt-1">Please select and upload an operational Excel file using the form above to display graphs and JSON statistical summaries.</p>
        </div>

        <div x-show="isLoading" x-cloak class="bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-16 text-center transition-colors">
            <div class="flex flex-col items-center justify-center gap-4">
                <svg class="h-12 w-12 animate-spin text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Analyzing data...</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">This may take a few moments while charts are generated.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</div>
</body>
</html>