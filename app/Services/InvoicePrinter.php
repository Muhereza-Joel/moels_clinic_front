<?php

namespace App\Services;

use TCPDF;
use App\Models\Invoice;
use Illuminate\Support\Facades\Storage;

class InvoicePrinter
{
    protected TCPDF $pdf;
    protected Invoice $invoice;
    protected array $config;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $this->config = config('invoice.printing', []);
        $this->setupPDF();
    }

    protected function setupPDF(): void
    {
        // Set document information
        $this->pdf->SetCreator(config('app.name'));
        $this->pdf->SetAuthor(config('app.name'));
        $this->pdf->SetTitle('Invoice ' . $this->invoice->invoice_number);
        $this->pdf->SetSubject('Invoice');

        // Set margins
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetHeaderMargin(5);
        $this->pdf->SetFooterMargin(10);

        // Set auto page breaks
        $this->pdf->SetAutoPageBreak(true, 25);

        // Add a page
        $this->pdf->AddPage();
    }

    public function generate(): string
    {
        $this->addHeader();
        $this->addPatientInfo();
        $this->addInvoiceDetails();
        $this->addItemsTable();
        $this->addTotals();
        $this->addFooter();

        return $this->pdf->Output('invoice_' . $this->invoice->invoice_number . '.pdf', 'S');
    }

    protected function addHeader(): void
    {
        $this->pdf->SetFont('helvetica', 'B', 20);
        $this->pdf->Cell(0, 10, 'INVOICE', 0, 1, 'C');

        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 5, config('app.name'), 0, 1, 'C');
        $this->pdf->Cell(0, 5, config('app.address'), 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'Phone: ' . config('app.phone'), 0, 1, 'C');

        $this->pdf->Ln(10);
    }

    protected function addPatientInfo(): void
    {
        $patient = $this->invoice->patient;

        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 8, 'BILL TO:', 0, 1);

        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 5, $patient->full_name, 0, 1);
        if ($patient->phone) {
            $this->pdf->Cell(0, 5, 'Phone: ' . $patient->phone, 0, 1);
        }
        if ($patient->email) {
            $this->pdf->Cell(0, 5, 'Email: ' . $patient->email, 0, 1);
        }

        $this->pdf->Ln(10);
    }

    protected function addInvoiceDetails(): void
    {
        $this->pdf->SetFont('helvetica', '', 10);

        $data = [
            ['Invoice Number:', $this->invoice->invoice_number],
            ['Invoice Date:', $this->invoice->issued_at?->format('d/m/Y')],
            ['Due Date:', $this->invoice->due_at?->format('d/m/Y')],
            ['Status:', strtoupper($this->invoice->status)],
        ];

        foreach ($data as $row) {
            $this->pdf->Cell(40, 6, $row[0], 0, 0);
            $this->pdf->Cell(0, 6, $row[1], 0, 1);
        }

        $this->pdf->Ln(10);
    }

    protected function addItemsTable(): void
    {
        // Table header
        $this->pdf->SetFont('helvetica', 'B', 10);
        $headers = ['Description', 'Unit Price', 'Qty', 'Total'];
        $widths = [90, 30, 20, 30];

        for ($i = 0; $i < count($headers); $i++) {
            $this->pdf->Cell($widths[$i], 8, $headers[$i], 1, 0, 'C');
        }
        $this->pdf->Ln();

        // Table rows
        $this->pdf->SetFont('helvetica', '', 9);
        foreach ($this->invoice->invoiceItems as $item) {
            $this->pdf->Cell($widths[0], 8, $item->description, 'LR', 0, 'L');
            $this->pdf->Cell($widths[1], 8, number_format($item->unit_price, 2), 'LR', 0, 'R');
            $this->pdf->Cell($widths[2], 8, $item->quantity, 'LR', 0, 'C');
            $this->pdf->Cell($widths[3], 8, number_format($item->total_amount, 2), 'LR', 0, 'R');
            $this->pdf->Ln();
        }

        // Table footer
        $this->pdf->Cell(array_sum($widths), 0, '', 'T');
        $this->pdf->Ln(10);
    }

    protected function addTotals(): void
    {
        $this->pdf->SetFont('helvetica', '', 10);

        $totals = [
            ['Subtotal:', $this->invoice->subtotal_amount],
            ['Tax (' . ($this->invoice->tax_rate * 100) . '%):', $this->invoice->tax_amount],
            ['Discount:', $this->invoice->discount_amount],
        ];

        // Right align totals
        $this->pdf->SetX(120);

        foreach ($totals as $total) {
            $this->pdf->Cell(50, 7, $total[0], 0, 0, 'R');
            $this->pdf->Cell(30, 7, number_format($total[1], 2), 0, 1, 'R');
        }

        // Total line
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetX(120);
        $this->pdf->Cell(50, 10, 'TOTAL:', 0, 0, 'R');
        $this->pdf->Cell(30, 10, number_format($this->invoice->total_amount, 2), 0, 1, 'R');

        $this->pdf->Ln(10);
    }

    protected function addFooter(): void
    {
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->SetY(-25);

        $footer = "Thank you for your business!\n";
        $footer .= "Payment terms: Net 30 days\n";
        $footer .= "For any inquiries, please contact: " . config('app.email');

        $this->pdf->MultiCell(0, 4, $footer, 0, 'C');

        // Page number
        $this->pdf->SetY(-12);
        $this->pdf->Cell(0, 10, 'Page ' . $this->pdf->getAliasNumPage() . '/' . $this->pdf->getAliasNbPages(), 0, 0, 'C');
    }

    public function saveToDisk(string $path = 'invoices'): string
    {
        $filename = 'invoice_' . $this->invoice->invoice_number . '_' . time() . '.pdf';
        $fullPath = $path . '/' . $filename;

        $pdfContent = $this->generate();
        Storage::put($fullPath, $pdfContent);

        return $fullPath;
    }
}
