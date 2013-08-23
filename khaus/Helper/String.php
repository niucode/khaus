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
 * @version     1:20120822
 */

class Khaus_Helper_String
{
    static public function counTrim($string, $charlist = null, $total = 0)
    {
        if ($total > 0) {
            $charlist = is_null($charlist) ? "\n \t" : $charlist;
            for ($i = 0; $i < $total; $i++) {
                if (strstr($charlist, substr($string, 0, 1))) {
                    $string = substr($string, 1);
                }
                if (strstr($charlist, substr($string, -1, 1))) {
                    $string = substr($string, 0, -1);
                }
            }
            return $string;
        } else {
            return trim($string, $charlist);
        }
    }
    static public function substrword($string, $chars)
    {
        $string = substr($string, 0, $chars);
        if (strlen($string) == $chars) {
            $string = preg_replace('/\s[^\s]$/', '', $string);
        }
        return $string;
    }
    
    static public function newsCutter($string, $chars)
    {
        $string = preg_replace('/\<br\s*\>/i', " ", $string);
        $string = strip_tags($string);
        $string = html_entity_decode($string);
        return self::substrword($string, $chars);
    }
}