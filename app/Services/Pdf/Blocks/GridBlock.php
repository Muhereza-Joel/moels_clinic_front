<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;

class GridBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $columns = $block['columns'];
        $items = $block['items'];
        $colWidth = 180 / $columns;

        foreach ($items as $i => $item) {
            $x = ($i % $columns) * $colWidth;
            $pdf->SetX($x + 15);

            $renderer = app(\App\Services\Pdf\TemplateRenderer::class);
            $renderer->renderBlock($pdf, $item, $context);

            if (($i + 1) % $columns === 0) {
                $pdf->Ln();
            }
        }
    }
}
