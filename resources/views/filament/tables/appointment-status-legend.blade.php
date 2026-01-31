<div class="mb-2 rounded-lg border border-gray-200 bg-white p-4 text-sm shadow-sm
            dark:border-gray-700 dark:bg-gray-900">

    <!-- Legend Title -->
    <div class="mb-2">
        <span class="font-semibold text-gray-900 dark:text-gray-100">Key:</span>
        <span class="text-gray-600 dark:text-gray-400">Appointment states and their meanings</span>
    </div>

    <!-- Scrollable Legend Items -->
    <div class="flex gap-x-6 gap-y-3 overflow-x-auto whitespace-nowrap pb-2">

        <!-- Pending -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="appointment-legend-dot appointment-legend-pending"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Pending – Awaiting confirmation</span>
        </div>

        <!-- Confirmed -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="appointment-legend-dot appointment-legend-confirmed"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Confirmed – Appointment scheduled</span>
        </div>

        <!-- Checked In -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="appointment-legend-dot appointment-legend-checked-in"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Checked In – Patient has arrived</span>
        </div>

        <!-- Completed -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="appointment-legend-dot appointment-legend-completed"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Completed – Appointment finished</span>
        </div>

        <!-- Cancelled -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="appointment-legend-dot appointment-legend-cancelled"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Cancelled – Appointment called off</span>
        </div>

        <!-- No Show -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="appointment-legend-dot appointment-legend-no-show"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">No Show – Patient did not attend</span>
        </div>

    </div>
</div>