<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus
 * @version     1:20120822
 */

function debug($elements) {printf('<pre class="debug">%s</pre>', print_r($elements, true));}
function __autoload($classname)
{
    $path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    $classname = preg_replace('/^khaus_/i', '', $classname);
    $classname = str_replace('_', DIRECTORY_SEPARATOR, $classname) . '.php';
    include_once $path . $classname;
}