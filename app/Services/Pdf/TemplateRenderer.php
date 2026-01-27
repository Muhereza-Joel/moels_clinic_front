<?php

namespace App\Services\Pdf;

use TCPDF;
use App\Models\PdfTemplate;

class TemplateRenderer
{
    /**
     * Render a PdfTemplate model (DB or default file).
     */
    public function renderTemplate(PdfTemplate $template, array $context): TCPDF
    {
        // Use the helper on the model to resolve DB layout or fallback to default JSON
        $layout = $template->getResolvedLayout();

        return $this->render($layout, $context);
    }

    /**
     * Render a raw layout array.
     */
    public function render(array $layout, array $context): TCPDF
    {
        $pdf = new TCPDF();
        $pdf->AddPage();

        foreach ($layout['sections'] as $section) {
            $pdf->Ln();
            $pdf->SetFont('', 'B', 12);
            $pdf->Write(0, $this->resolve($section['title'], $context));
            $pdf->Ln();

            $columns = $section['grid']['columns'];
            $items = $section['grid']['items'];
            $colWidth = 180 / $columns;

            foreach ($items as $i => $item) {
                $x = ($i % $columns) * $colWidth;
                $pdf->SetX($x + 15);
                $this->renderBlock($pdf, $item, $context);

                if (($i + 1) % $columns === 0) {
                    $pdf->Ln();
                }
            }
        }

        $pdf->Ln();
        $pdf->Write(0, $this->resolve($layout['footer']['text'], $context));

        return $pdf;
    }

    private function resolve($value, $context)
    {
        if (is_array($value)) {
            $lang = app()->getLocale();
            return $value[$lang] ?? reset($value);
        }
        return (new VariableResolver())->resolve($value, $context);
    }

    public function renderBlock($pdf, $block, $context)
    {
        switch ($block['type']) {
            case 'text':
                $pdf->Write(0, $this->resolve($block['content'], $context));
                break;
            case 'conditional':
                if ($this->resolve($block['condition'], $context)) {
                    $this->renderBlock($pdf, $block['block'], $context);
                }
                break;
            case 'json_table':
                $this->renderJsonTable($pdf, $context['data_json']);
                break;
        }
    }

    private function renderJsonTable($pdf, $data)
    {
        foreach ($data as $key => $value) {
            $pdf->Write(0, "$key: $value");
            $pdf->Ln();
        }
    }
}
