<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;
use App\Services\Pdf\VariableResolver;

class TableBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $resolver = new VariableResolver();
        $columns = $block['columns'];
        $rows = $block['rows'];

        foreach ($rows as $row) {
            $resolvedRow = array_map(fn($cell) => $resolver->resolve($cell, $context), $row);
            $pdf->Cell(60, 10, $resolvedRow[0], 1);
            $pdf->Cell(120, 10, $resolvedRow[1], 1);
            $pdf->Ln();
        }
    }
}
