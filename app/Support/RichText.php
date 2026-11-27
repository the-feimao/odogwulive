<?php

namespace App\Support;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;

/**
 * Renders rich text from Trix/Summernote without trusting the stored HTML.
 *
 * Only a small formatting allowlist survives; every attribute is removed
 * except safe link URLs. Plain text still gets newline-to-br handling.
 */
class RichText
{
    private const ALLOWED_TAGS = [
        'p', 'div', 'br', 'hr',
        'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'ul', 'ol', 'li', 'blockquote',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'pre', 'code', 'a',
    ];

    private const DROP_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'math',
        'form', 'input', 'button', 'select', 'textarea',
        'video', 'audio', 'source', 'track', 'link', 'meta', 'base',
    ];

    public static function safeHtml(mixed $value): string
    {
        $original = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($original === '') {
            return '';
        }

        // Plain text keeps the old line-break behaviour. This also covers
        // older rows that never went through a rich-text editor.
        if (preg_match('/<\s*\/?\s*[a-z][^>]*>/i', $original) !== 1) {
            return nl2br(e($original), false);
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);
        $wrapperId = 'ds-rich-'.bin2hex(random_bytes(6));

        $document->loadHTML(
            '<?xml encoding="UTF-8"?><body><div id="'.$wrapperId.'">'.$original.'</div></body>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        $root = (new DOMXPath($document))->query('//div[@id="'.$wrapperId.'"]')->item(0);

        if (! $root instanceof DOMElement) {
            return e(strip_tags($original));
        }

        foreach (iterator_to_array($root->childNodes) as $child) {
            self::cleanNode($child, $document);
        }

        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $document->saveHTML($child);
        }

        return trim($html);
    }

    private static function cleanNode(DOMNode $node, DOMDocument $document): void
    {
        if ($node instanceof DOMComment) {
            self::removeNode($node);

            return;
        }

        if (! $node instanceof DOMElement) {
            // Text nodes are kept as-is; DOMDocument serialises them safely.
            return;
        }

        $tag = strtolower($node->tagName);

        if (in_array($tag, self::DROP_TAGS, true)) {
            self::removeNode($node);

            return;
        }

        $href = $tag === 'a' ? trim($node->getAttribute('href')) : null;

        while ($node->attributes !== null && $node->attributes->length > 0) {
            $node->removeAttributeNode($node->attributes->item(0));
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            self::cleanNode($child, $document);
        }

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            self::unwrapNode($node);

            return;
        }

        if ($tag === 'a' && $href !== null && $href !== '' && self::isSafeLink($href)) {
            $node->setAttribute('href', $href);
            $node->setAttribute('rel', 'noopener noreferrer nofollow');
            $node->setAttribute('target', '_blank');
        }
    }

    private static function isSafeLink(string $href): bool
    {
        return preg_match('~^(?:https?://|mailto:|#|/(?!/))~i', $href) === 1;
    }

    private static function removeNode(DOMNode $node): void
    {
        $node->parentNode?->removeChild($node);
    }

    private static function unwrapNode(DOMNode $node): void
    {
        while ($node->firstChild !== null) {
            $node->parentNode?->insertBefore($node->firstChild, $node);
        }

        self::removeNode($node);
    }
}
