<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;

class JsonTableBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $data = $context['data_json'] ?? [];
        foreach ($data as $key => $value) {
            $pdf->Cell(60, 10, $key, 1);
            $pdf->Cell(120, 10, is_array($value) ? json_encode($value) : $value, 1);
            $pdf->Ln();
        }
    }
}
