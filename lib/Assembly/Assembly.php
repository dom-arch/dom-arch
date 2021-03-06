<?php
namespace DOMArch;

use StdClass;
use DOMArch\Request\Incoming;
use DOMNode;

abstract class Assembly
{
    protected $_node;

    protected function __construct(
        DOMNode $node
    )
    {
        $this->_node = $node;
    }

    protected function _translate(
        DOMNode $node
    )
    {
        $descendants = $node->query('.//text()[normalize-space()]')
            ->toArray();

        foreach ($descendants as $descendant) {
            $parent = $descendant->parentNode;

            if ($parent && $parent->nodeName !== 'textarea') {
                $descendant->translate();
            }
        }
    }

    protected function _translateAttr(
        DOMNode $node,
        string $attribute,
        $value = null
    )
    {
        $selector = './/*/@' . $attribute;

        if (!is_null($value)) {
            $selector .=  '="' . $value . '"';
        }

        $descendants = $node->query($selector)
            ->toArray();

        foreach ($descendants as $descendant) {
            $descendant->ownerElement->translateAttr($attribute);
        }
    }

    /**
     * @return DOMNode
     */
    public function getNode()
    {
        return $this->_node;
    }

    public function extract()
    {
        $values = [];
        $request = Incoming::requested();
        $max_file_size = Config::global()->get('common')->get('maxFileSize');
        $body_values = $request->getBody()->toArray();

        $form = $this->_node->select('form');

        foreach ($form->elements->toArray() as $element) {
            $attrset = $element->attrset;
            $name = $attrset->name;

            if (!$name) {
                continue;
            }

            if ($attrset->readonly) {
                $values[$name] = $element->value;

                continue;
            }

            if ($attrset->type === 'file') {
                $file = $_FILES[$name] ?? null;

                if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }

                if ($max_file_size < $file['size']) {
                    continue;
                }

                $accept = $attrset->accept;

                if (!$accept) {
                    $values[$name] = $file;

                    continue;
                }

                $formats = explode('|', $accept);
                $type = $file['type'];

                if (!in_array($type, $formats)) {
                    continue;
                }

                $type = substr($type, 0, strpos($type, '/')) . '/*';

                if (!in_array($type, $formats)) {
                    continue;
                }

                $file_object = new StdClass();
                $file_object->filename = $file['name'];
                $file_object->data = file_get_contents($file['tmp_name']);
                $file_object->type = $type;
                unlink($file['tmp_name']);

                $values[$name] = $file_object;
                
                continue;
            }

            $values[$name] = $body_values[$name] ?? null;
        }

        return $values;
    }
}
