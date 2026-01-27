<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;
use App\Services\Pdf\VariableResolver;

class ColoredBoxBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $resolver = new VariableResolver();
        $text = $resolver->resolve($block['content'], $context);

        [$r, $g, $b] = $block['color'] ?? [220, 220, 220];
        $pdf->SetFillColor($r, $g, $b);
        $pdf->MultiCell(0, 10, $text, 0, 'L', true);
    }
}
