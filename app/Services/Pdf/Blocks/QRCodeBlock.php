<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;

class QRCodeBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $value = $block['value'] ?? '';
        $pdf->write2DBarcode($value, 'QRCODE,H', '', '', 30, 30);
    }
}
