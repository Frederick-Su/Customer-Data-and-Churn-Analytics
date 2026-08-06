<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Analytics Dashboard</title>
    <!-- Tailwind CSS with Dark Mode Enabled -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <!-- Alpine.js for Tab Switching, Modal & Dark Mode Toggle -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ 
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
        activeTab: 'overview', 
        modalOpen: false, 
        modalImg: '', 
        modalTitle: '',
        openModal(url, title) {
            this.modalImg = url;
            this.modalTitle = title;
            this.modalOpen = true;
        }
     }" 
     x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
     :class="{ 'dark bg-slate-900 text-slate-100': darkMode, 'bg-slate-50 text-slate-800': !darkMode }"
     class="font-sans antialiased min-h-screen transition-colors duration-200">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        
        <!-- Header Section -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 dark:border-slate-800 pb-6 gap-4">
            <div class="flex items-center justify-between w-full md:w-auto">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">Analytics Dashboard</h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Upload an operational Excel file to generate multi-region customer insights.</p>
                </div>

                <!-- Dark Mode Toggle Button (Mobile) -->
                <button @click="darkMode = !darkMode" 
                        class="md:hidden p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-sm">
                    <span x-show="!darkMode" class="text-lg">🌙</span>
                    <span x-show="darkMode" x-cloak class="text-lg">☀️</span>
                </button>
            </div>
            
            <div class="flex items-center gap-3">
                <!-- Upload Form Card -->
                <form action="/analyze" method="POST" enctype="multipart/form-data" 
                      class="flex-1 md:flex-none flex items-center gap-3 bg-white dark:bg-slate-800 p-2 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                    @csrf
                    <input type="file" name="excel" accept=".xlsx,.xls" required 
                           class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-950/60 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 cursor-pointer">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg transition-colors text-sm shadow-sm whitespace-nowrap">
                        Analyze Data
                    </button>
                </form>

                <!-- Dark Mode Toggle Button (Desktop) -->
                <button @click="darkMode = !darkMode" 
                        title="Toggle Dark Mode"
                        class="hidden md:flex p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-sm items-center justify-center">
                    <span x-show="!darkMode" class="text-lg">🌙</span>
                    <span x-show="darkMode" x-cloak class="text-lg">☀️</span>
                </button>
            </div>
        </div>

        @if(isset($images) && count($images) > 0)
        <!-- Dashboard Tabs Container -->
        <div class="space-y-6">
            
            <!-- Folder Style Tab Header -->
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
                    Site Weekly Churn
                </button>
            </div>

            <!-- TAB 1: OVERVIEW & DEMOGRAPHICS -->
            <div x-show="activeTab === 'overview'" x-cloak class="space-y-6">
                @php $img = $images->firstWhere('name', '1_mosaic'); @endphp
                @if($img)
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-4">Mosaic Distribution Plot</h2>
                    <div class="overflow-hidden rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 cursor-pointer group"
                         @click="openModal('{{ $img['url'] }}', 'Mosaic Distribution Plot')">
                        <img src="{{ $img['url'] }}" alt="Mosaic Plot" class="w-full h-auto object-contain max-h-[500px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200">
                    </div>
                </div>
                @endif
            </div>

            <!-- TAB 2: TENURE & LOYALTY -->
            <div x-show="activeTab === 'tenure'" x-cloak class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Customer Tenure Analysis -->
                @php $imgTenure = $images->firstWhere('name', '2_tenure'); @endphp
                @if($imgTenure)
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between transition-colors">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Customer Tenure by Region</h2>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 mb-4 cursor-pointer group"
                             @click="openModal('{{ $imgTenure['url'] }}', 'Customer Tenure by Region')">
                            <img src="{{ $imgTenure['url'] }}" alt="Tenure Plot" class="w-full h-auto object-contain max-h-[350px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200">
                        </div>
                    </div>

                    <!-- Summary Table for 2_tenure.json -->
                    @if(isset($summaries['2_tenure']))
                    <div class="mt-4 border-t border-slate-100 dark:border-slate-700/60 pt-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-2">Regional Metrics Summary</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                                <thead class="bg-slate-100 dark:bg-slate-700/50 text-slate-700 dark:text-slate-200 font-medium">
                                    <tr>
                                        <th class="p-2 rounded-l">Metric</th>
                                        @foreach(array_keys($summaries['2_tenure']) as $stat)
                                            <th class="p-2">{{ ucfirst($stat) }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                    @php
                                        $regions = array_keys(reset($summaries['2_tenure']));
                                    @endphp
                                    @foreach($regions as $region)
                                    <tr>
                                        <td class="p-2 font-semibold text-slate-800 dark:text-slate-100">{{ $region }}</td>
                                        @foreach($summaries['2_tenure'] as $stat => $values)
                                            <td class="p-2">{{ $values[$region] ?? '-' }}</td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Site Loyalty Analysis -->
                @php $imgLoyalty = $images->firstWhere('name', '3_site_loyalty'); @endphp
                @if($imgLoyalty)
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between transition-colors">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Average Customer Loyalty by Site</h2>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 mb-4 cursor-pointer group"
                             @click="openModal('{{ $imgLoyalty['url'] }}', 'Average Customer Loyalty by Site')">
                            <img src="{{ $imgLoyalty['url'] }}" alt="Site Loyalty" class="w-full h-auto object-contain max-h-[350px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200">
                        </div>
                    </div>

                    <!-- Summary Table for 3_site_loyalty.json -->
                    @if(isset($summaries['3_site_loyalty']))
                    <div class="mt-4 border-t border-slate-100 dark:border-slate-700/60 pt-4">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-400 mb-2">Site Loyalty Breakdown</h3>
                        <div class="max-h-[180px] overflow-y-auto">
                            <table class="w-full text-xs text-left text-slate-600 dark:text-slate-300">
                                <thead class="bg-slate-100 dark:bg-slate-700/50 text-slate-700 dark:text-slate-200 font-medium sticky top-0">
                                    <tr>
                                        <th class="p-2">Site</th>
                                        <th class="p-2">Customers</th>
                                        <th class="p-2">Avg Tenure (Months)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                    @foreach($summaries['3_site_loyalty']['Customers'] as $site => $count)
                                    <tr>
                                        <td class="p-2 font-medium text-slate-800 dark:text-slate-100">{{ $site }}</td>
                                        <td class="p-2">{{ $count }}</td>
                                        <td class="p-2">{{ $summaries['3_site_loyalty']['Avg_Tenure'][$site] ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- TAB 3: ACTIVE & CHURN TRENDS -->
            <div x-show="activeTab === 'activity'" x-cloak class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @php $imgActive = $images->firstWhere('name', '4_active_customers'); @endphp
                    @if($imgActive)
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Active Customers Over Time</h2>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 cursor-pointer group"
                             @click="openModal('{{ $imgActive['url'] }}', 'Active Customers Over Time')">
                            <img src="{{ $imgActive['url'] }}" alt="Active Customers" class="w-full h-auto object-contain max-h-[400px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200">
                        </div>
                    </div>
                    @endif

                    @php $imgChurn = $images->firstWhere('name', '5_churn_percentage'); @endphp
                    @if($imgChurn)
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3">Monthly Churn Percentage</h2>
                        <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/50 p-2 cursor-pointer group"
                             @click="openModal('{{ $imgChurn['url'] }}', 'Monthly Churn Percentage')">
                            <img src="{{ $imgChurn['url'] }}" alt="Churn Percentage" class="w-full h-auto object-contain max-h-[400px] mx-auto rounded-lg group-hover:scale-[1.01] transition-transform duration-200">
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- TAB 4: SITE WEEKLY CHURN -->
            <div x-show="activeTab === 'weekly'" x-cloak class="space-y-6">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                    <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-1">Weekly Churn Rate by Site</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs mb-6">Historical trends across active sites over the last 6 months.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($images->filter(fn($i) => str_contains($i['name'], '_weekly_churn')) as $siteImg)
                        @php $siteTitle = str_replace(['6_', '_weekly_churn'], '', $siteImg['name']) . ' Weekly Churn'; @endphp
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 bg-slate-50/50 dark:bg-slate-900/30 hover:shadow-md transition-shadow">
                            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                {{ str_replace(['6_', '_weekly_churn'], '', $siteImg['name']) }}
                            </h3>
                            <div class="bg-white dark:bg-slate-800 rounded-lg p-2 border border-slate-100 dark:border-slate-700/50 cursor-pointer group"
                                 @click="openModal('{{ $siteImg['url'] }}', '{{ $siteTitle }}')">
                                <img src="{{ $siteImg['url'] }}" alt="{{ $siteImg['name'] }}" class="w-full h-auto object-contain max-h-[280px] rounded group-hover:scale-[1.01] transition-transform duration-200">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Lightbox Modal -->
            <div x-show="modalOpen" 
                 x-cloak 
                 @keydown.escape.window="modalOpen = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-sm"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                 
                <!-- Click Outside Overlay -->
                <div class="absolute inset-0" @click="modalOpen = false"></div>

                <!-- Animated Modal Box -->
                <div x-show="modalOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-90"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90"
                     class="relative z-10 max-w-5xl w-full bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700">
                    
                    <!-- Modal Header -->
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/50">
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100" x-text="modalTitle"></h3>
                        <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors rounded-lg p-1 hover:bg-slate-200/60 dark:hover:bg-slate-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Image Container -->
                    <div class="p-4 sm:p-6 bg-slate-900/5 dark:bg-slate-950/40 flex items-center justify-center min-h-[300px]">
                        <img :src="modalImg" :alt="modalTitle" class="max-h-[80vh] w-auto object-contain rounded-lg shadow-md">
                    </div>
                </div>
            </div>

        </div>
        @else
        <!-- Empty State Container -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-12 text-center my-10 transition-colors">
            <div class="inline-flex p-4 rounded-full bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">No Analytics Loaded</h3>
            <p class="text-slate-500 dark:text-slate-400 text-sm max-w-md mx-auto mt-1">Please select and upload an operational Excel file using the form above to display graphs and JSON statistical summaries.</p>
        </div>
        @endif

    </div>

</body>
</html>