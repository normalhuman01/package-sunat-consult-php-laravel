<?php
namespace MrJmpl3\LaravelPeruConsult\Classes\Parsers;

use DOMDocument;
use DOMXPath;

class XpathLoader
{
    public static function getXpathFromHtml(string $html): DOMXPath
    {
        $dom = new DOMDocument();
        $prevState = libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($prevState);

        return new DOMXPath($dom);
    }
}
