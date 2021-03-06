<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/DOMArch
*/
namespace DOMArch\View;

use DOMArch\Config;
use DOMArch\Request;
use Exception;

use PHPDOM;
use PHPDOM\HTML\NodeList;
use PHPDOM\HTML\SelectorCache;

abstract class HTML
    extends PHPDOM\HTML\Document
{
    protected $_nodeClasses = [
        '\\DOMNode' => HTML\Node::class,
        '\\DOMElement' => HTML\Element::class,
        '\\DOMText' => HTML\Text::class,
        '\\DOMComment' => '\\PHPDOM\\HTML\\Comment',
        '\\DOMDocumentFragment' => '\\PHPDOM\\HTML\\DocumentFragment'
    ];

    public function __construct()
    {
        parent::__construct();

        SelectorCache::load();
    }

    abstract public function getTranslator();

    abstract public function getUrlTranslator();

    abstract protected function url(
        array $params = [],
        string $fragment = ''
    );

    public function translate(
        $translatable,
        ...$params
    )
    {
        $locale = $this->lang;
        $translator = $this->getTranslator();

        if (is_string($translatable)) {
            $translatable = $this->createTextNode($translatable);
        }

        switch ($translatable->nodeType) {
            case 1: {
                $element = null;
                $locale = $translatable->getAttribute('lang') ?: $locale;
                $children = $translatable->query('.//text()[normalize-space()]')
                    ->toArray();

                foreach ($children as $child) {
                    $text = trim($child->nodeValue);

                    if (!empty($text)) {
                        $element = $child;
                        break;
                    }
                }

                $listener = function(
                    $translation,
                    $id,
                    $is_translated
                ) use ($element) {
                    $parent = $element->parentNode;
                    $document = $parent->ownerDocument;
                    $text_node = $document->createTextNode($translation);

                    if (!$is_translated) {
                        $parent->classList->add('untranslated');
                        $parent->dataset->translationId = $id;
                    }

                    if ($parent->nodeName === 'title') {
                        $element->nodeValue = $translation;

                        return;
                    }

                    $parent->insert($text_node, $element);
                    $element->remove();
                };

                break;
            }

            case 2: {
                $name = $translatable->name;
                $element = $translatable->ownerElement;
                $locale = $element->getAttribute('lang') ?: $locale;

                if ($name === 'href' || $name === 'data-href') {
                    $locale = $element->getAttribute('hreflang') ?: $locale;
                }

                $is_route = in_array($name, ['action', 'href', 'data-href', 'src']);

                if ($is_route) {
                    return $this->translateUrl($translatable, $locale);
                }

                $text = $translatable->value;

                $listener = function(
                    $translation,
                    $id,
                    $is_translated
                ) use ($element, $name) {
                    if (!$is_translated) {
                        $element->classList->add('untranslated');
                        $data_name = 'translation-' . $name . 'Id';
                        $element->dataset->{$data_name} = $id;
                    }

                    $element->setAttribute($name, $translation);
                };

                break;
            }

            case 3: {
                $text = $translatable->nodeValue;
                $element = $translatable;

                $listener = function(
                    $translation,
                    $id,
                    $is_translated
                ) use ($element) {
                    if (!$is_translated) {
                        $parent = $element->parentNode;
                        $parent->classList->add('untranslated');
                        $parent->dataset->translationId = $id;
                    }

                    $element->nodeValue = $translation;
                };

                break;
            }
        }

        if (empty($text) || empty($element)) {
            return $translatable;
        }

        $translator->translate($text, $locale, $listener, ...$params);

        return $translatable;
    }

    public function translateAttr(
        $translatable,
        string $attribute,
        ...$params
    )
    {
        $node = $translatable->getAttributeNode($attribute);

        if (empty($node) || empty($node->nodeValue)) {
            throw new Exception('Unable to translate node attribute');
        }

        return $this->translate($node, ...$params);
    }

    public function translateUrl(
        \DOMAttr $translatable,
        string $locale
    )
    {
        $translator = $this->getUrlTranslator();
        $Url = get_class(Request\Incoming::current()->getUrl());
        $url = $Url::parse($translatable->nodeValue);
        $name = $translatable->name;
        $element = $translatable->ownerElement;
        $method = $url->getMethod() ?? 'get';

        if ($name === 'action' && !$method) {
            $method = $element->getAttribute('method');
        }

        $url->setMethod(strtolower($method));

        $translator->translate($url, $locale, function(
            $translation,
            $id,
            $is_translated
        ) use ($element, $name) {
            if (!$is_translated) {
                $element->classList->add('untranslated');
                $data_name = 'translation-' . $name . 'Id';
                $element->dataset->{$data_name} = $id;
            }

            $element->setAttribute($name, $translation);
        });

        return $element;
    }

    private function _translate()
    {
        if ($this->getTranslator()) {
            $this->getTranslator()->fetch();
        }

        if ($this->getUrlTranslator()) {
            $this->getUrlTranslator()->fetch();
        }

        return $this;
    }

    public function print()
    {
        $document = $this->_translate();

        $nodes = new NodeList($this->xpath->query('//*[not(*)][not(normalize-space())]'));

        foreach ($nodes->toArray() as $node) {
            if (!in_array($node->nodeName, $this->_unbreakables)) {
                $node->remove();
            }
        }

        $html = (string) $document;

        header('Content-Type: text/html;charset=' . $this->_encoding);
        header('Content-Length: ' . strlen($html));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Thu, 01 Jan 1970 00:00:00');

        echo $html;

        exit();
    }

    public function __destruct()
    {
        SelectorCache::save();
    }
}
