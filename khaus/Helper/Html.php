<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Helper
 * @version     1:20120823
 */

class Khaus_Helper_Html
{
    static public function uriSchemeCheck($uri)
    {
        $schemeAccepted = array('http://', 'https://', 'ftp://');
        foreach ($schemeAccepted as $scheme) {
            $length = strlen($scheme);
            if (!strncasecmp($scheme, $uri, $length)) {
                return $uri;
            }
        }
        return (string) $schemeAccepted[0] . $uri;
    }
    
    static public function documentPath()
    {
        $path = $_SERVER['PHP_SELF'];
        $path = dirname($path);
        return $path;
    }
}