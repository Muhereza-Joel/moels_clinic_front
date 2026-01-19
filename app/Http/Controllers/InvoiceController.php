<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoicePrinter;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function print(Invoice $invoice)
    {
        $printer = new InvoicePrinter($invoice);
        $pdfContent = $printer->generate();

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="invoice_' . $invoice->invoice_number . '.pdf"');
    }

    public function preview(Invoice $invoice)
    {
        $printer = new InvoicePrinter($invoice);
        $pdfContent = $printer->generate();

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="preview_' . $invoice->invoice_number . '.pdf"');
    }

    public function download(Invoice $invoice)
    {
        $printer = new InvoicePrinter($invoice);
        $pdfContent = $printer->generate();

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice_' . $invoice->invoice_number . '.pdf"');
    }
}
