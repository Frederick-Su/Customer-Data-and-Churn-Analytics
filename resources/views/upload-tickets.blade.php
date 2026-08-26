<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNET Ticketing Analytics</title>

    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@700,500,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> [x-cloak] { display: none !important; } </style>
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
>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 dark:border-slate-800 pb-6 gap-4">
        <div class="flex items-center justify-between w-full md:w-auto">
            <div>
                <a href="/" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 mb-3 transition-colors">
                    <span class="mr-1">&larr;</span> Back to Portal
                </a>
                <h1 class="text-3xl font-bold tracking-tight">Ticketing Analytics</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Upload your ticketing export to generate agent performance and complaint visualizations.</p>
            </div>

            <button @click="darkMode = !darkMode" class="md:hidden p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-sm">
                <span x-show="!darkMode" class="text-lg">🌙</span>
                <span x-show="darkMode" x-cloak class="text-lg">☀️</span>
            </button>
        </div>

        <div class="flex items-center gap-3">
            <form action="/analyze-tickets" method="POST" enctype="multipart/form-data" @submit="isLoading = true"
                  class="flex-1 md:flex-none flex items-center gap-3 bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                @csrf
                <input type="file" name="excel" accept=".xlsx,.xls,.csv" required
                       class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/60 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 cursor-pointer">
                <button type="submit" :disabled="isLoading" class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 disabled:cursor-not-allowed text-white font-medium px-5 py-2 rounded-lg transition-colors text-sm shadow-sm whitespace-nowrap flex items-center justify-center gap-2">
                    <span x-text="isLoading ? 'Analyzing...' : 'Analyze Data'"></span>
                </button>
            </form>

            <button @click="darkMode = !darkMode" title="Toggle Dark Mode" class="hidden md:flex p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-sm items-center justify-center">
                <span x-show="!darkMode" class="text-lg">⏾</span>
                <span x-show="darkMode" x-cloak class="text-lg">𖤓</span>
            </button>
        </div>
    </div>

    @if(isset($images) && count($images) > 0)

    <!-- Map the new Python outputs to PHP variables -->
    @php
        $imgTicketsByArea = $images->firstWhere('name', '1_tickets_by_area');
        $imgTicketsByComplaint = $images->firstWhere('name', '2_tickets_by_complaint');
        $imgTopVnIds = $images->firstWhere('name', '3_top_vn_ids');
        $imgDurationArea = $images->firstWhere('name', '4_median_duration_area');
        $imgDurationComplaint = $images->firstWhere('name', '5_duration_by_complaint');
        $imgDurationDist = $images->firstWhere('name', '6_duration_distribution');
        $imgComplaintProp = $images->firstWhere('name', '7_complaint_proportion');
        $imgMonthlyVolume = $images->firstWhere('name', '8_monthly_volume');
        $imgHeatmap = $images->firstWhere('name', '9_complaint_heatmap');

        $dashboard = $summaries['0_dashboard_cards'] ?? [];
    @endphp

    <!-- Dashboard Cards -->
    @if(!empty($dashboard))
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Rows Evaluated</p>
            <h2 class="mt-2 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($dashboard['Total_Rows']) }}</h2>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Unique Tickets</p>
            <h2 class="mt-2 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($dashboard['Unique_Tickets']) }}</h2>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Duplicate Tickets</p>
            <h2 class="mt-2 text-3xl font-bold text-rose-600 dark:text-rose-400">{{ number_format($dashboard['Duplicate_Tickets']) }}</h2>
        </div>
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Unique VN IDs (Customers)</p>
            <h2 class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($dashboard['Unique_VN_IDs']) }}</h2>
        </div>
    </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="space-y-6">
        <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800 pb-1">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'" class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Overview & Volume
            </button>
            <button @click="activeTab = 'duration'" :class="activeTab === 'duration' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'" class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Resolution & Duration
            </button>
            <button @click="activeTab = 'hotspots'" :class="activeTab === 'hotspots' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 bg-white dark:bg-slate-800 font-semibold shadow-sm' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300 dark:hover:border-slate-700'" class="px-5 py-3 border-b-2 text-sm rounded-t-lg transition-all flex items-center gap-2">
                Hotspots & Deep Dive
            </button>
        </div>

        <!-- TAB 1: Overview & Volume -->
        <div x-show="activeTab === 'overview'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if($imgMonthlyVolume)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 lg:col-span-2">
                <h3 class="font-bold text-lg mb-4">Monthly Ticket Volume Trend</h3>
                <img src="{{ $imgMonthlyVolume['url'] }}" class="w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgMonthlyVolume['url'] }}', 'Monthly Volume')">
            </div>
            @endif

            @if($imgTicketsByArea)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5">
                <h3 class="font-bold text-lg mb-4">Tickets by Area</h3>
                <img src="{{ $imgTicketsByArea['url'] }}" class="w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgTicketsByArea['url'] }}', 'Tickets by Area')">
            </div>
            @endif

            @if($imgTicketsByComplaint)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5">
                <h3 class="font-bold text-lg mb-4">Tickets by Complaint Type</h3>
                <img src="{{ $imgTicketsByComplaint['url'] }}" class="w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgTicketsByComplaint['url'] }}', 'Tickets by Complaint')">
            </div>
            @endif
            
            @if($imgComplaintProp)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 lg:col-span-2 flex flex-col items-center">
                <h3 class="font-bold text-lg mb-4 w-full">Proportion of Tickets by Complaint</h3>
                <img src="{{ $imgComplaintProp['url'] }}" class="max-w-xl w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgComplaintProp['url'] }}', 'Complaint Proportion')">
            </div>
            @endif
        </div>

        <!-- TAB 2: Resolution & Duration -->
        <div x-show="activeTab === 'duration'" x-cloak class="grid grid-cols-1 gap-6">
            @if($imgDurationDist)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5">
                <h3 class="font-bold text-lg mb-4">Ticket Duration Spread by Complaint Type</h3>
                <img src="{{ $imgDurationDist['url'] }}" class="w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgDurationDist['url'] }}', 'Duration Spread')">
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @if($imgDurationArea)
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5">
                    <h3 class="font-bold text-lg mb-4">Median Duration by Area</h3>
                    <img src="{{ $imgDurationArea['url'] }}" class="w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgDurationArea['url'] }}', 'Duration by Area')">
                </div>
                @endif

                @if($imgDurationComplaint)
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5">
                    <h3 class="font-bold text-lg mb-4">Median Duration by Complaint Type</h3>
                    <img src="{{ $imgDurationComplaint['url'] }}" class="w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgDurationComplaint['url'] }}', 'Duration by Complaint')">
                </div>
                @endif
            </div>
        </div>

        <!-- TAB 3: Hotspots & Deep Dive -->
        <div x-show="activeTab === 'hotspots'" x-cloak class="grid grid-cols-1 gap-6">
            @if($imgHeatmap)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5">
                <h3 class="font-bold text-lg mb-4">Complaint Concentration across Areas</h3>
                <img src="{{ $imgHeatmap['url'] }}" class="w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgHeatmap['url'] }}', 'Heatmap')">
            </div>
            @endif

            @if($imgTopVnIds)
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5">
                <h3 class="font-bold text-lg mb-4">Top 20 VN IDs (Repeat Complainers)</h3>
                <img src="{{ $imgTopVnIds['url'] }}" class="w-full rounded-xl cursor-pointer hover:opacity-90" @click="openModal('{{ $imgTopVnIds['url'] }}', 'Top VN IDs')">
            </div>
            @endif
        </div>
        
        <!-- Image Modal -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/80 p-4 backdrop-blur-sm" x-cloak>
            <div @click.away="modalOpen = false" class="bg-white dark:bg-slate-800 p-2 rounded-2xl shadow-2xl max-w-5xl w-full relative">
                <button @click="modalOpen = false" class="absolute -top-4 -right-4 bg-white dark:bg-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-300 dark:hover:text-white rounded-full p-2 shadow-lg border border-slate-200 dark:border-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
                <div class="p-4 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="font-bold text-lg" x-text="modalTitle"></h3>
                </div>
                <div class="p-4 overflow-auto max-h-[80vh]">
                    <img :src="modalImg" class="w-full rounded-xl">
                </div>
            </div>
        </div>

    </div>
    @else
    <!-- Empty State -->
    <div class="my-10">
        <div x-show="!isLoading" x-cloak class="bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center transition-colors">
            <div class="inline-flex p-4 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">No Ticketing Analytics Loaded</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto mt-1">Please select and upload an operational Excel file using the form above to display ticketing charts and summaries.</p>
        </div>

        <div x-show="isLoading" x-cloak class="bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-16 text-center transition-colors">
            <div class="flex flex-col items-center justify-center gap-4">
                <svg class="h-12 w-12 animate-spin text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Analyzing data...</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">This may take a few moments while your charts are generated.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</body>
</html>