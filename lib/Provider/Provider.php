<?php
namespace DOMArch;

use DOMArch\Request\Outcoming;

abstract class Provider
{
    protected $_request;
    protected $_module;
    protected $_moduleId;
    protected $_className;
    protected $_classId;
    protected $_key;

    protected function __construct(Outcoming $request)
    {
        $this->_request = $request;
    }

    public function getRequest()
    {
        return $this->_request;
    }

    public function setModule(string $module)
    {
        $this->_module = $module;

        return $this;
    }

    public function getModule()
    {
        return $this->_module;
    }

    public function setModuleId(int $module_id)
    {
        $this->_moduleId = $module_id;

        return $this;
    }

    public function getModuleId()
    {
        return $this->_moduleId;
    }

    public function setClassName(string $name)
    {
        $this->_className = $name;

        return $this;
    }

    public function getClassName()
    {
        return $this->_className;
    }

    public function setClassId(int $id)
    {
        $this->_classId = $id;

        return $this;
    }

    public function getClassId()
    {
        return $this->_classId;
    }

    public function addConstraint(string $name, $value)
    {
        $params = $this->getRequest()
            ->getUrl()
            ->getParams();

        if (substr($name, -2) === '[]') {
            $params->add(substr($name, 0, -2), $value);
        } else {
            $params->set($name, $value);
        }

        return $this;
    }

    public function query(array $params)
    {
        foreach ($params as $name => $value) {
            $this->addConstraint($name, $value);
        }

        return $this;
    }

    public function addHeader(string $name, string $value)
    {
        $this->getRequest()
            ->getHeaders()->set($name, $value);

        return $this;
    }

    public function addHeaders(array $headers)
    {
        foreach ($headers as $name => $value) {
            $this->addHeader($name, $value);
        }

        return $this;
    }

    public function get()
    {
        $this->getRequest()
            ->setMethod('get');

        return $this;
    }

    public function post(array $fields = [])
    {
        $this->getRequest()
            ->setMethod('post')
            ->setBody(http_build_query($fields));

        return $this;
    }

    public function put(array $fields = [])
    {
        $this->getRequest()
            ->setMethod('put')
            ->setBody(http_build_query($fields));

        return $this ?? false;
    }

    public function patch(array $fields = [])
    {
        $this->getRequest()
            ->setMethod('patch')
            ->setBody(http_build_query($fields));

        return $this;
    }

    public function delete()
    {
        $this->getRequest()
            ->setMethod('delete');

        return $this;
    }

    public function fetch()
    {
        return $this->_build()
            ->getRequest()
            ->fetch($this->_key);
    }

    public function getKey()
    {
        return $this->_key;
    }

    public function limit(int $limit)
    {
        return $this->addConstraint('$limit', $limit);
    }

    public function offset(int $offset)
    {
        return $this->addConstraint('$offset', $offset);
    }

    public function page(int $number, int $limit)
    {
        return $this
            ->limit($limit)
            ->offset(($number - 1) * $limit);
    }

    public function asc(string $field)
    {
        return $this->addConstraint('$order[]', [$field, 'ASC']);
    }

    public function desc(string $field)
    {
        return $this->addConstraint('$order[]', [$field, 'DESC']);
    }

    public function sort(string $field = null, string $order = null)
    {
        if ($field && $order) {
            return $this->{$order}($field);
        }

        return $this;
    }

    public function gt(string $field, $value)
    {
        return $this->addConstraint('$gt[]', [$field, $value]);
    }

    public function gte(string $field, $value)
    {
        return $this->addConstraint('$gte[]', [$field, $value]);
    }

    public function lt(string $field, $value)
    {
        return $this->addConstraint('$lt[]', [$field, $value]);
    }

    public function lte(string $field, $value)
    {
        return $this->addConstraint('$lte[]', [$field, $value]);
    }

    public function between(string $field, $min, $max)
    {
        return $this
            ->gte($field, $min)
            ->lte($field, $max);
    }

    public function isNull(string $field)
    {
        return $this->addConstraint('$null[]', $field);
    }

    public function like(string $field, $value)
    {
        return $this->addConstraint('$like[]', [$field, $value]);
    }

    public function contains(string $field, $value)
    {
        return $this->like($field, '%' . $value . '%');
    }

    public function starts(string $field, $value)
    {
        return $this->like($field, $value . '%');
    }

    public function ends(string $field, $value)
    {
        return $this->like($field, '%' . $value);
    }

    public function in(string $field, array $values)
    {
        return $this->addConstraint('$in[]', [$field, $values]);
    }

    protected function _build()
    {
        $values = [
            $this->getModule(),
            $this->getModuleId(),
            $this->getClassName(),
            $this->getClassId()
        ];

        $path = '';

        foreach ($values as $value) {
            if (is_null($value)) {
                continue;
            }

            $path .= '/' . $value;
        }

        $this->getRequest()
            ->getUrl()
            ->setPath($path);

        return $this;
    }
}
