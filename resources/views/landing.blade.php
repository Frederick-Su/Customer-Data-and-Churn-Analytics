<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNET-Analytics Portal</title>

    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@700,500,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body
    x-data="{
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    }"
    x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
    :class="{ 'dark bg-slate-900 text-slate-100': darkMode, 'bg-slate-50 text-slate-800': !darkMode }"
    class="font-['Satoshi',sans-serif] antialiased min-h-screen transition-colors duration-200 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
>

    <!-- Theme Toggle (Top Right) -->
    <div class="absolute top-6 right-6">
        <button @click="darkMode = !darkMode"
                class="p-3 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shadow-sm flex items-center justify-center">
            <span x-show="!darkMode" class="text-xl">⏾</span>
            <span x-show="darkMode" x-cloak class="text-xl">𖤓</span>
        </button>
    </div>

    <!-- Header -->
    <div class="text-center max-w-2xl mx-auto mb-16 mt-10">
        <div class="inline-flex items-center justify-center p-3 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-2xl mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
        </div>
        <h1 class="text-4xl md:text-5xl font-bold tracking-tight text-slate-900 dark:text-white mb-4">VNET-Analytics Portal</h1>
        <p class="text-lg text-slate-600 dark:text-slate-400">Select an analytics module below to upload your data and generate automated insights, visualizations, and summaries.</p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 w-full max-w-5xl">
        
        <!-- Customer Data Analysis Card -->
        <a href="/customer-analysis" 
           class="group relative bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-200 dark:border-slate-700 hover:border-indigo-500 dark:hover:border-indigo-400 shadow-sm hover:shadow-xl hover:shadow-indigo-500/10 transition-all duration-300 flex flex-col items-center text-center overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-indigo-50/50 to-transparent dark:from-indigo-950/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="relative z-10">
                <div class="w-16 h-16 mx-auto bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Customer Data</h2>
                <p class="text-slate-600 dark:text-slate-400 mb-6 text-sm">Analyze tenure, revenue, churn percentages, cohort lifetime value, and demographic distribution of your active customers.</p>
                <span class="inline-flex items-center text-sm font-semibold text-indigo-600 dark:text-indigo-400 group-hover:gap-2 transition-all">
                    Launch Module <span class="tracking-tighter">-></span>
                </span>
            </div>
        </a>

        <!-- Ticketing Data Analysis Card -->
        <a href="/ticket-analysis" 
           class="group relative bg-white dark:bg-slate-800 p-8 rounded-3xl border border-slate-200 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 shadow-sm hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-300 flex flex-col items-center text-center overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-emerald-50/50 to-transparent dark:from-emerald-950/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="relative z-10">
                <div class="w-16 h-16 mx-auto bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">Ticketing Data</h2>
                <p class="text-slate-600 dark:text-slate-400 mb-6 text-sm">Review support ticket volumes, resolution times, common issues, and agent performance metrics.</p>
                <span class="inline-flex items-center text-sm font-semibold text-emerald-600 dark:text-emerald-400 group-hover:gap-2 transition-all">
                    Launch Module <span class="tracking-tighter">-></span>
                </span>
            </div>
        </a>

    </div>
</body>
</html>