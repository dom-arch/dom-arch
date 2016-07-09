<?php
/*
Copyright 2015 Lcf.vs
 -
Released under the MIT license
 -
https://github.com/Lcfvs/DOMArch
*/
namespace DOMArch\Controller\Dev;

class Dump extends \DOMArch\Controller\Dev
{
    public function dump($value)
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($traces as $trace) {
            $class = $trace['class'] ?? null;
            $function = $trace['function'] ?? null;

            if ($class) {
                continue;
            }

            if ($function === 'dump') {
                break;
            }
        }
        
        $this->_view
            ->set('file', $trace['file'])
            ->set('line', $trace['line'])
            ->set('value', print_r($value, true));
    }
}