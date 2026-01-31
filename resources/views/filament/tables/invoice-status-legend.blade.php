<div class="mb-2 rounded-lg border border-gray-200 bg-white p-4 text-sm shadow-sm
            dark:border-gray-700 dark:bg-gray-900">

    <!-- Legend Title -->
    <div class="mb-2">
        <span class="font-semibold text-gray-900 dark:text-gray-100">Key:</span>
        <span class="text-gray-600 dark:text-gray-400">Invoice states and their meanings</span>
    </div>

    <!-- Scrollable Legend Items -->
    <div class="flex gap-x-6 gap-y-3 overflow-x-auto whitespace-nowrap pb-2">

        <!-- Draft -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="invoice-legend-dot invoice-legend-draft"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Draft – Invoice not yet finalized</span>
        </div>

        <!-- Issued -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="invoice-legend-dot invoice-legend-issued"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Issued – Invoice sent to client</span>
        </div>

        <!-- Partially Paid -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="invoice-legend-dot invoice-legend-partially-paid"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Partially Paid – Invoice with some amount received</span>
        </div>

        <!-- Paid -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="invoice-legend-dot invoice-legend-paid"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Paid – Invoice fully settled</span>
        </div>

        <!-- Void -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="invoice-legend-dot invoice-legend-void"></span>
            <span class="text-gray-700 text-xs dark:text-gray-300">Void – Invoice cancelled or invalid</span>
        </div>

    </div>
</div>