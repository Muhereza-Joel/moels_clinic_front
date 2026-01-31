<div class="mb-2 rounded-lg border border-gray-200 bg-white p-4 text-sm shadow-sm
            dark:border-gray-700 dark:bg-gray-900">

    <!-- Legend Title -->
    <div class="mb-2">
        <span class="font-semibold text-gray-900 dark:text-gray-100">Key:</span>
        <span class="text-gray-600 dark:text-gray-400">Lab Order states and their meanings</span>
    </div>

    <!-- Scrollable Legend Items -->
    <div class="flex gap-x-6 gap-y-3 overflow-x-auto whitespace-nowrap pb-2">

        <!-- Ordered -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="laborder-legend-dot laborder-legend-ordered"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Pending – Order placed but not started</span>
        </div>

        <!-- In Progress -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="laborder-legend-dot laborder-legend-in-progress"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">In Progress – Lab work is ongoing</span>
        </div>

        <!-- Completed -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="laborder-legend-dot laborder-legend-completed"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Completed – Lab work finished</span>
        </div>

        <!-- Cancelled -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="laborder-legend-dot laborder-legend-cancelled"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Cancelled – Order was called off</span>
        </div>

    </div>
</div>