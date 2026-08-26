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
            <form action="/analyze-tickets" method="POST" enctype="multipart/form-data" @submit="isLoading = true"
                  class="flex-1 md:flex-none flex items-center gap-3 bg-paper-100 dark:bg-graphite-900 p-2 rounded-sm border border-graphite-200 dark:border-graphite-700 transition-colors">
                @csrf
                <input type="file" name="excel" accept=".xlsx,.xls,.csv" required
                       class="block w-full text-xs font-mono text-graphite-500 dark:text-graphite-400 file:mr-3 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-mono file:font-medium file:uppercase file:tracking-wider file:bg-graphite-100 file:text-graphite-700 dark:file:bg-graphite-800 dark:file:text-graphite-300 hover:file:bg-graphite-200 dark:hover:file:bg-graphite-700 cursor-pointer">
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
            <p class="op-eyebrow text-graphite-400 dark:text-graphite-500">Unique VN IDs (Customers)</p>
            <h2 class="mt-2 font-mono text-2xl font-semibold text-good-600 dark:text-good-400">{{ number_format($dashboard['Unique_VN_IDs']) }}</h2>
        </div>
    </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="space-y-6">
        <div class="flex flex-wrap gap-1 border-b border-graphite-200 dark:border-graphite-800">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'" class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Overview &amp; Volume
            </button>
            <button @click="activeTab = 'duration'" :class="activeTab === 'duration' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'" class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Resolution &amp; Duration
            </button>
            <button @click="activeTab = 'hotspots'" :class="activeTab === 'hotspots' ? 'border-signal-600 dark:border-signal-500 text-signal-600 dark:text-signal-400 font-semibold' : 'border-transparent text-graphite-500 dark:text-graphite-400 hover:text-graphite-800 dark:hover:text-graphite-200'" class="px-4 py-3 border-b-2 font-mono text-xs uppercase tracking-wider transition-all">
                Hotspots &amp; Deep Dive
            </button>
        </div>

        <!-- TAB 1: Overview & Volume -->
        <div x-show="activeTab === 'overview'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @if($imgMonthlyVolume)
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm lg:col-span-2">
                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Monthly Ticket Volume Trend</h3>
                </div>
                <div class="p-4">
                    <img src="{{ $imgMonthlyVolume['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgMonthlyVolume['url'] }}', 'Monthly Volume')">
                </div>
            </div>
            @endif

            @if($imgTicketsByArea)
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Tickets by Area</h3>
                </div>
                <div class="p-4">
                    <img src="{{ $imgTicketsByArea['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgTicketsByArea['url'] }}', 'Tickets by Area')">
                </div>
            </div>
            @endif

            @if($imgTicketsByComplaint)
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Tickets by Complaint Type</h3>
                </div>
                <div class="p-4">
                    <img src="{{ $imgTicketsByComplaint['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgTicketsByComplaint['url'] }}', 'Tickets by Complaint')">
                </div>
            </div>
            @endif

            @if($imgComplaintProp)
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm lg:col-span-2 flex flex-col items-center">
                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800 w-full">
                    <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Proportion of Tickets by Complaint</h3>
                </div>
                <div class="p-4">
                    <img src="{{ $imgComplaintProp['url'] }}" class="max-w-xl w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgComplaintProp['url'] }}', 'Complaint Proportion')">
                </div>
            </div>
            @endif
        </div>

        <!-- TAB 2: Resolution & Duration -->
        <div x-show="activeTab === 'duration'" x-cloak class="grid grid-cols-1 gap-5">
            @if($imgDurationDist)
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Ticket Duration Spread by Complaint Type</h3>
                </div>
                <div class="p-4">
                    <img src="{{ $imgDurationDist['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgDurationDist['url'] }}', 'Duration Spread')">
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @if($imgDurationArea)
                <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
                    <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                        <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Median Duration by Area</h3>
                    </div>
                    <div class="p-4">
                        <img src="{{ $imgDurationArea['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgDurationArea['url'] }}', 'Duration by Area')">
                    </div>
                </div>
                @endif

                @if($imgDurationComplaint)
                <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
                    <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                        <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Median Duration by Complaint Type</h3>
                    </div>
                    <div class="p-4">
                        <img src="{{ $imgDurationComplaint['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgDurationComplaint['url'] }}', 'Duration by Complaint')">
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- TAB 3: Hotspots & Deep Dive -->
        <div x-show="activeTab === 'hotspots'" x-cloak class="grid grid-cols-1 gap-5">
            @if($imgHeatmap)
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Complaint Concentration across Areas</h3>
                </div>
                <div class="p-4">
                    <img src="{{ $imgHeatmap['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgHeatmap['url'] }}', 'Heatmap')">
                </div>
            </div>
            @endif

            @if($imgTopVnIds)
            <div class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm">
                <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <h3 class="op-eyebrow text-graphite-500 dark:text-graphite-400">Top 20 VN IDs (Repeat Complainers)</h3>
                </div>
                <div class="p-4">
                    <img src="{{ $imgTopVnIds['url'] }}" class="w-full rounded-sm cursor-pointer hover:opacity-90" @click="openModal('{{ $imgTopVnIds['url'] }}', 'Top VN IDs')">
                </div>
            </div>
            @endif
        </div>

        <!-- Image Modal -->
        <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-graphite-950/85 p-4 backdrop-blur-sm" x-cloak>
            <div @click.away="modalOpen = false" class="bg-paper-100 dark:bg-graphite-900 rounded-sm shadow-2xl max-w-5xl w-full relative border border-graphite-200 dark:border-graphite-700">
                <button @click="modalOpen = false" class="absolute -top-4 -right-4 bg-paper-100 dark:bg-graphite-800 text-graphite-500 hover:text-signal-600 dark:text-graphite-300 dark:hover:text-signal-400 rounded-sm p-2 shadow-lg border border-graphite-200 dark:border-graphite-600">
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
                <svg class="h-9 w-9 animate-spin text-signal-600 dark:text-signal-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <h3 class="font-mono text-sm font-semibold uppercase tracking-wider text-graphite-700 dark:text-graphite-300">Processing Dataset&hellip;</h3>
                    <p class="text-graphite-500 dark:text-graphite-400 text-sm mt-2">This may take a few moments while your charts are generated.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</body>
</html>