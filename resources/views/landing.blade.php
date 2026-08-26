<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNET-Analytics Portal</title>

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
            background-image: radial-gradient(rgba(122,130,140,0.22) 1px, transparent 1px);
            background-size: 24px 24px;
        }
        ::selection { background: #D98E2B; color: #0A0C10; }
    </style>
</head>
<body
    x-data="{
        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
    }"
    x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
    :class="{ 'dark bg-graphite-950 text-graphite-100': darkMode, 'bg-paper-50 text-graphite-900': !darkMode }"
    class="font-body antialiased min-h-screen transition-colors duration-200"
>

    <!-- Console Header -->
    <div class="border-b border-graphite-200 dark:border-graphite-800">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="w-1.5 h-1.5 rounded-full bg-good-500"></span>
                <span class="font-mono text-[11px] uppercase tracking-[0.18em] text-graphite-500 dark:text-graphite-400">
                    VNET-Analytics &middot; Internal Tooling
                </span>
            </div>

            <button @click="darkMode = !darkMode"
                    class="p-2 rounded-sm border border-graphite-200 dark:border-graphite-700 text-graphite-500 dark:text-graphite-400 hover:border-signal-600 hover:text-signal-600 dark:hover:border-signal-400 dark:hover:text-signal-400 transition-colors">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                </svg>
                <svg x-show="darkMode" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M4.5 12H3M21 12h-1.5M5.6 5.6l1.06 1.06M17.34 17.34l1.06 1.06M5.6 18.4l1.06-1.06M17.34 6.66l1.06-1.06M16.5 12a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                </svg>
            </button>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">

        <!-- Header -->
        <div class="max-w-2xl mb-14">
            <p class="font-mono text-xs uppercase tracking-[0.18em] text-signal-600 dark:text-signal-400 mb-3">
                Module Select
            </p>
            <h1 class="font-display text-4xl md:text-5xl font-semibold tracking-tight mb-4">
                VNET-Analytics Portal
            </h1>
            <p class="text-graphite-600 dark:text-graphite-400 leading-relaxed">
                Select a module, upload a dataset, and generate the corresponding metrics, charts, and statistical summaries.
            </p>
        </div>

        <!-- Module Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 w-full">

            <!-- Customer Data Module -->
            <a href="/customer-analysis"
               class="group relative bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 hover:border-signal-600 dark:hover:border-signal-500 rounded-sm transition-colors">
                <div class="flex items-center justify-between px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <span class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Module</span>
                    <span class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">CX&ndash;01</span>
                </div>

                <div class="p-6">
                    <div class="w-10 h-10 flex items-center justify-center border border-graphite-300 dark:border-graphite-700 group-hover:border-signal-600 dark:group-hover:border-signal-500 rounded-sm text-graphite-600 dark:text-graphite-300 group-hover:text-signal-600 dark:group-hover:text-signal-400 mb-5 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h2 class="font-display text-xl font-semibold mb-2">Customer Data</h2>
                    <p class="text-sm text-graphite-500 dark:text-graphite-400 mb-6 leading-relaxed">
                        Tenure, revenue, churn rate, cohort lifetime value, and regional distribution of active customers.
                    </p>
                    <span class="inline-flex items-center gap-1.5 font-mono text-xs uppercase tracking-wider text-signal-600 dark:text-signal-400">
                        Open Module <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </span>
                </div>
            </a>

            <!-- Ticketing Data Module -->
            <a href="/ticket-analysis"
               class="group relative bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 hover:border-signal-600 dark:hover:border-signal-500 rounded-sm transition-colors">
                <div class="flex items-center justify-between px-5 py-3 border-b border-graphite-200 dark:border-graphite-800">
                    <span class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Module</span>
                    <span class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">TK&ndash;01</span>
                </div>

                <div class="p-6">
                    <div class="w-10 h-10 flex items-center justify-center border border-graphite-300 dark:border-graphite-700 group-hover:border-signal-600 dark:group-hover:border-signal-500 rounded-sm text-graphite-600 dark:text-graphite-300 group-hover:text-signal-600 dark:group-hover:text-signal-400 mb-5 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <h2 class="font-display text-xl font-semibold mb-2">Ticketing Data</h2>
                    <p class="text-sm text-graphite-500 dark:text-graphite-400 mb-6 leading-relaxed">
                        Support ticket volume, resolution duration, complaint concentration, and repeat-complaint accounts.
                    </p>
                    <span class="inline-flex items-center gap-1.5 font-mono text-xs uppercase tracking-wider text-signal-600 dark:text-signal-400">
                        Open Module <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </span>
                </div>
            </a>

        </div>
    </div>
</body>
</html>