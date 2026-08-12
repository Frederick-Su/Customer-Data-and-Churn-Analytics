<div x-show="activeTab === 'relationships'" x-cloak>

    @php
        $summary = $summaries['8_duration_vs_price'] ?? [];
    @endphp

    <div class="space-y-6">

        <!-- Graph -->
        @if($imgDurationPrice)

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">

                <div class="mb-5">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">
                        Contract Duration vs Price
                    </h2>

                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Relationship between invoice-to-expiry duration and customer price.
                    </p>
                </div>

                <img
                    src="{{ $imgDurationPrice['url'] }}"
                    alt="Contract Duration vs Price"
                    class="w-full rounded-xl cursor-pointer"
                    @click="openModal('{{ $imgDurationPrice['url'] }}', 'Contract Duration vs Price')"
                >

            </div>

        @endif


        <!-- Statistical Summary -->
        @if(!empty($summary))

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">

                <!-- Customers -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">

                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Customers Analyzed
                    </p>

                    <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">
                        {{ number_format($summary['Customers'] ?? 0) }}
                    </h3>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Active customers included in the analysis.
                    </p>

                </div>


                <!-- Correlation -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">

                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Pearson Correlation
                    </p>

                    <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">
                        {{ number_format($summary['Correlation']['Pearson_R'] ?? 0, 4) }}
                    </h3>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Strength of the relationship between duration and price.
                    </p>

                </div>


                <!-- R Squared -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">

                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        R²
                    </p>

                    <h3 class="mt-2 text-2xl font-bold text-slate-900 dark:text-slate-100">
                        {{ number_format($summary['Correlation']['R_Squared'] ?? 0, 4) }}
                    </h3>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Variation in price explained by duration.
                    </p>

                </div>


                <!-- Relationship -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-5 shadow-sm">

                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                        Relationship
                    </p>

                    <h3 class="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $summary['Interpretation']['Relationship'] ?? 'N/A' }}
                    </h3>

                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                        Based on Pearson correlation.
                    </p>

                </div>

            </div>


            <!-- Detailed Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- Contract Duration -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">

                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4">
                        Contract Duration
                    </h3>

                    <div class="space-y-3">

                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Average</span>
                            <span class="font-semibold">
                                {{ number_format($summary['Contract_Duration']['Average_Days'] ?? 0, 2) }} days
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Median</span>
                            <span class="font-semibold">
                                {{ number_format($summary['Contract_Duration']['Median_Days'] ?? 0, 2) }} days
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Minimum</span>
                            <span class="font-semibold">
                                {{ number_format($summary['Contract_Duration']['Minimum_Days'] ?? 0, 2) }} days
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Maximum</span>
                            <span class="font-semibold">
                                {{ number_format($summary['Contract_Duration']['Maximum_Days'] ?? 0, 2) }} days
                            </span>
                        </div>

                    </div>

                </div>


                <!-- Price -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">

                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4">
                        Price

                    </h3>

                    <div class="space-y-3">

                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Average</span>
                            <span class="font-semibold">
                                {{ number_format($summary['Price']['Average'] ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Median</span>
                            <span class="font-semibold">
                                {{ number_format($summary['Price']['Median'] ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Minimum</span>
                            <span class="font-semibold">
                                {{ number_format($summary['Price']['Minimum'] ?? 0, 2) }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500 dark:text-slate-400">Maximum</span>
                            <span class="font-semibold">
                                {{ number_format($summary['Price']['Maximum'] ?? 0, 2) }}
                            </span>
                        </div>

                    </div>

                </div>

            </div>


            <!-- Statistical Significance -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">

                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4">
                    Statistical Analysis
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            P-value
                        </p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ $summary['Correlation']['P_Value'] ?? 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Regression Slope
                        </p>

                        <p class="mt-1 text-lg font-semibold">
                            {{ number_format($summary['Regression']['Slope'] ?? 0, 4) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                            Statistically Significant
                        </p>

                        <p class="mt-1 text-lg font-semibold
                            {{ ($summary['Interpretation']['Statistically_Significant'] ?? false)
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-slate-500 dark:text-slate-400' }}">

                            {{ ($summary['Interpretation']['Statistically_Significant'] ?? false)
                                ? 'Yes'
                                : 'No' }}

                        </p>
                    </div>

                </div>

            </div>

        @endif

    </div>

</div>