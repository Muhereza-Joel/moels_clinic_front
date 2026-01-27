<?php

namespace App\Services\Pdf\Blocks;

use TCPDF;
use App\Services\Pdf\VariableResolver;

class ConditionalBlock implements BlockInterface
{
    public function render(TCPDF $pdf, array $block, array $context): void
    {
        $resolver = new VariableResolver();
        $condition = $resolver->resolve($block['condition'], $context);

        if (!empty($condition)) {
            $renderer = app(\App\Services\Pdf\TemplateRenderer::class);
            $renderer->renderBlock($pdf, $block['block'], $context);
        }
    }
}
