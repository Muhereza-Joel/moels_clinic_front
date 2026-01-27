<?php

namespace App\Services\Pdf;

class VariableResolver
{
    public function resolve(string $template, array $context): string
    {
        return preg_replace_callback('/{{(.*?)}}/', function ($matches) use ($context) {
            return data_get($context, trim($matches[1]), '');
        }, $template);
    }
}
