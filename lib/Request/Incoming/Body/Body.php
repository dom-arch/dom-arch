<?php
namespace DOMArch\Request\Incoming;

use DOMArch\Request;
use DOMArch\Set;

class Body
{
    use Set;

    /**
     * @param Request\Incoming $request
     * @return $this|null
     */
    public static function fromRequest(Request\Incoming $request)
    {
        if (!in_array($request->getMethod(), ['patch', 'post', 'put'])) {
            return null;
        }

        if ($request !== Request\Incoming::requested()) {
            return Request\Incoming::requested()->getBody();
        }

        $content_type = $request->getHeaders()->get('Content-Type');

        if ($content_type === 'application/json') {
            return static::fromJSON(file_get_contents('php://input'));
        }
        
        return static::fromArray($_POST);
    }
}
