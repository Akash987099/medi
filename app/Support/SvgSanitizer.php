<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class SvgSanitizer
{
    public static function sanitize(string $svg): string
    {
        $document = new \DOMDocument();
        $previousErrorState = libxml_use_internal_errors(true);
        $loaded = $document->loadXML($svg, LIBXML_NONET | LIBXML_NOBLANKS | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorState);

        if (!$loaded || !$document->documentElement || $document->documentElement->localName !== 'svg' || $document->doctype) {
            throw ValidationException::withMessages([
                'svg_code' => 'Enter valid SVG markup.',
            ]);
        }

        $allowedElements = ['svg', 'g', 'path', 'circle', 'rect', 'line', 'polyline', 'polygon', 'ellipse'];
        $allowedAttributes = [
            'viewbox', 'width', 'height', 'x', 'y', 'x1', 'y1', 'x2', 'y2',
            'cx', 'cy', 'r', 'rx', 'ry', 'd', 'points', 'fill', 'stroke',
            'stroke-width', 'fill-rule', 'clip-rule', 'opacity', 'fill-opacity',
            'stroke-opacity', 'stroke-linecap', 'stroke-linejoin',
        ];
        self::sanitizeNode($document->documentElement, $allowedElements, $allowedAttributes);
        $sanitizedSvg = $document->saveXML($document->documentElement);

        if ($sanitizedSvg === false) {
            throw ValidationException::withMessages([
                'svg_code' => 'The SVG markup could not be processed.',
            ]);
        }

        return $sanitizedSvg;
    }

    private static function sanitizeNode(\DOMElement $element, array $allowedElements, array $allowedAttributes): void
    {
        for ($index = $element->attributes->length - 1; $index >= 0; $index--) {
            $attribute = $element->attributes->item($index);
            $attributeName = $attribute ? strtolower($attribute->name) : '';
            $validColor = $attribute
                && preg_match('/^(none|currentColor|#[a-fA-F0-9]{3,8}|[a-zA-Z]{1,20})$/D', $attribute->value) === 1;

            if (
                !$attribute
                || !in_array($attributeName, $allowedAttributes, true)
                || (in_array($attributeName, ['fill', 'stroke'], true) && !$validColor)
            ) {
                if ($attribute) {
                    $element->removeAttributeNode($attribute);
                }
            }
        }

        for ($index = $element->childNodes->length - 1; $index >= 0; $index--) {
            $child = $element->childNodes->item($index);
            if (!$child instanceof \DOMElement || !in_array($child->localName, $allowedElements, true)) {
                $element->removeChild($child);
                continue;
            }

            self::sanitizeNode($child, $allowedElements, $allowedAttributes);
        }
    }
}
