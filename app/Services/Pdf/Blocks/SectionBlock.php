<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;
use App\Services\Pdf\VariableResolver;

class SectionBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $resolver = new VariableResolver();
        $title = $resolver->resolve($block['title'], $context);

        $pdf->SetFont('', 'B', 12);
        $pdf->Write(0, $title);
        $pdf->Ln();

        foreach ($block['items'] as $item) {
            $renderer = app(\App\Services\Pdf\TemplateRenderer::class);
            $renderer->renderBlock($pdf, $item, $context);
        }
    }
}
