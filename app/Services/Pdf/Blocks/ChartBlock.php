<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;

class ChartBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        // For now, just render placeholder text  (Later you can integrate with a chart library to render images and embed them.)
        $pdf->Write(0, "Chart placeholder: " . json_encode($block['data']));
        $pdf->Ln();
    }
}
