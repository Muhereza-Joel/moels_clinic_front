<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;

interface BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void;
}
