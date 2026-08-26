{{--
    Lifetime Value tab.

    Reads $summaries['9_cohort_ltv'] (produced by analyze.py -> 9_cohort_ltv.json),
    a flat list of records like:
        { "CohortMonth": "2023-01", "CohortPeriod": 0, "Avg_Cumulative_LTV": 150000.0, "Customers": 42 }

    Grouped client-side into one line per signup cohort: X axis = months since
    signup, Y axis = average cumulative revenue per customer in that cohort.
--}}
<div x-show="activeTab === 'ltv'" x-cloak
     x-data="ltvChart(@js($summaries['9_cohort_ltv'] ?? []))"
     x-init="
        $watch('activeTab', (val) => { if (val === 'ltv') ensureChart() });
        $watch('darkMode', () => updateTheme());
        if (activeTab === 'ltv') ensureChart();
     "
     class="bg-paper-100 dark:bg-graphite-900 border border-graphite-200 dark:border-graphite-800 rounded-sm transition-colors">

    <div class="px-5 py-3 border-b border-graphite-200 dark:border-graphite-800 flex items-start justify-between flex-wrap gap-2">
        <div>
            <h3 class="font-mono text-[11px] uppercase tracking-[0.14em] text-graphite-400 dark:text-graphite-500">Customer LTV by Cohort</h3>
            <p class="text-xs text-graphite-500 dark:text-graphite-400 mt-1">
                Average cumulative revenue per customer, grouped by signup month, plotted against months since signup.
                Cohorts smaller than 10 customers are excluded.
            </p>
        </div>
        <p class="font-mono text-[11px] text-graphite-400 dark:text-graphite-500"
           x-show="cohorts.length > 0"
           x-text="activeCount(cohorts) + ' of ' + cohorts.length + ' cohorts shown'"></p>
    </div>

    <div class="p-5">

    <template x-if="records.length === 0">
        <p class="text-sm text-graphite-500 dark:text-graphite-400 py-16 text-center font-mono">
            No cohort LTV data available for this dataset.
        </p>
    </template>

    <div x-show="cohorts.length > 0" class="flex items-center flex-wrap gap-2 mb-4">
        <button @click="setAll(true)"
                class="font-mono text-[11px] uppercase font-medium px-2.5 py-1 rounded-sm border border-graphite-300 dark:border-graphite-600 text-graphite-500 dark:text-graphite-400 hover:border-signal-600 dark:hover:border-signal-500 transition-colors">
            All
        </button>
        <button @click="setAll(false)"
                class="font-mono text-[11px] uppercase font-medium px-2.5 py-1 rounded-sm border border-graphite-300 dark:border-graphite-600 text-graphite-500 dark:text-graphite-400 hover:border-signal-600 dark:hover:border-signal-500 transition-colors">
            None
        </button>

        <span class="w-px h-4 bg-graphite-200 dark:bg-graphite-700 mx-1"></span>

        <template x-for="cohort in cohorts" :key="cohort">
            <button @click="toggleCohort(cohort)"
                    :style="activeCohorts[cohort]
                        ? `background:${colors[cohort]}1a; border-color:${colors[cohort]}; color:${colors[cohort]}`
                        : ''"
                    :class="activeCohorts[cohort]
                        ? 'font-semibold'
                        : 'text-graphite-400 dark:text-graphite-500 border-graphite-200 dark:border-graphite-700 opacity-60 hover:opacity-100'"
                    class="font-mono text-[11px] px-2.5 py-1 rounded-sm border transition-all flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full shrink-0" :style="`background:${colors[cohort]}`"></span>
                <span x-text="cohort"></span>
            </button>
        </template>
    </div>

    <div x-show="records.length > 0" class="relative" style="height: 480px;">
        <canvas x-ref="canvas"></canvas>
    </div>
    </div>
</div>

<script>
    function ltvChart(rawRecords) {
        // Kept as a plain closure variable, deliberately NOT on `this`/the
        // returned object. Alpine wraps everything it returns from x-data in
        // a reactive Proxy (via @vue/reactivity's `reactive()`), and Chart.js
        // instances don't reliably survive that wrapping — calls like
        // setDatasetVisibility()/update() can silently no-op through the
        // proxy even though the surrounding Alpine state updates fine. A
        // closure variable never gets touched by Alpine's reactivity system,
        // so methods below always operate on the real instance.
        let chartInstance = null;

        return {
            records: rawRecords || [],
            cohorts: [],
            colors: {},
            activeCohorts: {},

            ensureChart() {
                if (this.records.length === 0) return;

                if (chartInstance) {
                    this.$nextTick(() => chartInstance.resize());
                    return;
                }

                this.$nextTick(() => this.buildChart());
            },

            buildChart() {
                const canvas = this.$refs.canvas;
                if (!canvas) return;

                const cohorts = [...new Set(this.records.map(r => r.CohortMonth))].sort();
                this.cohorts = cohorts;

                const palette = this.generateColors(cohorts);
                cohorts.forEach(c => {
                    this.colors[c] = palette[c];
                    // Default every cohort to visible the first time it's seen.
                    // Guarded so a later rebuild (if ever triggered) won't
                    // clobber toggles the user already made.
                    if (!(c in this.activeCohorts)) {
                        this.activeCohorts[c] = true;
                    }
                });

                const datasets = cohorts.map((cohortMonth) => {
                    const points = this.records
                        .filter(r => r.CohortMonth === cohortMonth)
                        .sort((a, b) => a.CohortPeriod - b.CohortPeriod)
                        .map(r => ({ x: r.CohortPeriod, y: r.Avg_Cumulative_LTV }));

                    return {
                        label: cohortMonth,
                        data: points,
                        borderColor: palette[cohortMonth],
                        backgroundColor: palette[cohortMonth],
                        fill: false,
                        tension: 0.15,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        hidden: !this.activeCohorts[cohortMonth],
                    };
                });

                const isDark = document.body.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(10,12,16,0.08)';
                const textColor = isDark ? '#A7ACB4' : '#565D66';
                const fontFamily = "'IBM Plex Mono', monospace";

                chartInstance = new Chart(canvas.getContext('2d'), {
                    type: 'line',
                    data: { datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'nearest', intersect: false },
                        plugins: {
                            // The cohort chips above the chart are the filter UI now,
                            // so the built-in legend stays off to avoid two controls
                            // doing the same job.
                            legend: { display: false },
                            tooltip: {
                                titleFont: { family: fontFamily },
                                bodyFont: { family: fontFamily },
                                callbacks: {
                                    label: (ctx) => `${ctx.dataset.label}: ${Math.round(ctx.parsed.y).toLocaleString()} at month ${ctx.parsed.x}`
                                }
                            }
                        },
                        scales: {
                            x: {
                                type: 'linear',
                                title: { display: true, text: 'Months Since Signup', color: textColor, font: { family: fontFamily } },
                                ticks: { stepSize: 1, precision: 0, color: textColor, font: { family: fontFamily } },
                                grid: { color: gridColor }
                            },
                            y: {
                                title: { display: true, text: 'Avg. Cumulative LTV', color: textColor, font: { family: fontFamily } },
                                ticks: {
                                    color: textColor,
                                    font: { family: fontFamily },
                                    callback: (v) => v.toLocaleString()
                                },
                                grid: { color: gridColor }
                            }
                        }
                    }
                });
            },

            // Flip a single cohort on/off. Uses Chart.js's own visibility API
            // instead of rebuilding the dataset array, so toggling is instant
            // and doesn't disturb the other lines' state or the zoom/pan level.
            toggleCohort(cohort) {
                this.activeCohorts[cohort] = !this.activeCohorts[cohort];

                if (!chartInstance) return;

                const idx = this.cohorts.indexOf(cohort);
                if (idx === -1) return;

                chartInstance.setDatasetVisibility(idx, this.activeCohorts[cohort]);
                chartInstance.update();
            },

            // Bulk show/hide every cohort at once (the "All" / "None" chips).
            setAll(state) {
                this.cohorts.forEach((cohort, idx) => {
                    this.activeCohorts[cohort] = state;
                    if (chartInstance) {
                        chartInstance.setDatasetVisibility(idx, state);
                    }
                });

                if (chartInstance) {
                    chartInstance.update();
                }
            },

            // How many of the given cohorts are currently toggled on —
            // used for the "X of Y cohorts shown" caption.
            activeCount(list) {
                return list.filter(c => this.activeCohorts[c]).length;
            },

            updateTheme() {
                if (!chartInstance) return;

                const isDark = document.body.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(10,12,16,0.08)';
                const textColor = isDark ? '#A7ACB4' : '#565D66';

                ['x', 'y'].forEach(axis => {
                    const scale = chartInstance.options.scales[axis];
                    scale.grid.color = gridColor;
                    scale.ticks.color = textColor;
                    scale.title.color = textColor;
                });

                chartInstance.update();
            },

            // One hue per calendar year (parsed from the "YYYY-MM" cohort
            // string), so cohorts group by color at a glance. Months within
            // the same year share that hue but get a spread of lightness
            // values, since making every month in a year pixel-identical
            // would make individual lines impossible to tell apart on the
            // chart itself. Saturation is kept moderate (50%) rather than
            // vivid so the palette reads as instrumentation, not confetti.
            generateColors(cohorts) {
                const years = [...new Set(cohorts.map(c => c.slice(0, 4)))].sort();

                const hueForYear = {};
                years.forEach((y, i) => {
                    hueForYear[y] = Math.round((360 / Math.max(years.length, 1)) * i);
                });

                const monthsByYear = {};
                cohorts.forEach(c => {
                    const y = c.slice(0, 4);
                    (monthsByYear[y] = monthsByYear[y] || []).push(c);
                });

                const colors = {};
                Object.keys(monthsByYear).forEach(y => {
                    const months = monthsByYear[y];
                    const hue = hueForYear[y];

                    months.forEach((cohort, idx) => {
                        const lightness = months.length > 1
                            ? 35 + Math.round((30 / (months.length - 1)) * idx)
                            : 48;
                        colors[cohort] = `hsl(${hue}, 50%, ${lightness}%)`;
                    });
                });

                return colors;
            }
        }
    }
</script>