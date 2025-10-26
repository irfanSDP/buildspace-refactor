<?php namespace PCK\Helpers\ContentMinifier;

use PCK\Helpers\ContentMinifier\HtmlMinifier;
use PCK\Helpers\ContentMinifier\JsMinifier;

class Minify
{
    public static function html(string $html, array $options = []) : string
    {
        $options = array_merge([
            'jsMinifier' => array(JsMinifier::class, 'minify')
        ], $options);

        $min = new HtmlMinifier($html, $options);

        return $min->process();
    }
}