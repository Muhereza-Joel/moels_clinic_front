<div class="mb-2 rounded-lg border border-gray-200 bg-white p-4 text-sm shadow-sm
            dark:border-gray-700 dark:bg-gray-900">

    <!-- Legend Title -->
    <div class="mb-2">
        <span class="font-semibold text-gray-900 dark:text-gray-100">Key:</span>
        <span class="text-gray-600 dark:text-gray-400">Admission states and their meanings</span>
    </div>

    <!-- Scrollable Legend Items -->
    <div class="flex gap-x-6 gap-y-3 overflow-x-auto whitespace-nowrap pb-2">

        <!-- Active -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="admission-legend-dot admission-legend-active"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Active – Patient currently admitted</span>
        </div>

        <!-- Discharged -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="admission-legend-dot admission-legend-discharged"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Discharged – Patient released from care</span>
        </div>

        <!-- Transferred -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="admission-legend-dot admission-legend-transferred"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Transferred – Patient moved to another facility</span>
        </div>

    </div>
</div>