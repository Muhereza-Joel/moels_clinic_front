<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;

class SignatureBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $pdf->Ln(10);
        $pdf->Write(0, "Signature: ______________________");
        $pdf->Ln();
    }
}
