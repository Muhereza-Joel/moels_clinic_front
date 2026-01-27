<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;
use App\Services\Pdf\VariableResolver;

class TextBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $resolver = new VariableResolver();
        $text = $resolver->resolve($block['content'], $context);
        $pdf->Write(0, $text);
        $pdf->Ln();
    }
}
