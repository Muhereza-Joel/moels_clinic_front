<?php

namespace App\Services\Pdf;

class VariableResolver
{
    public function resolve(?string $template, array $context): string
    {
        if (empty($template)) {
            return '';
        }

        return preg_replace_callback('/\{\{([\w\.]+)\}\}/', function ($matches) use ($context) {
            return data_get($context, $matches[1], '');
        }, $template);
    }
}
