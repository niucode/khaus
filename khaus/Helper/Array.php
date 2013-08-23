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

class Khaus_Helper_Array
{
    static public function arrayFlatten(array $array, $toLower = false)
    {
        $newArray = array();
        $array = new RecursiveArrayIterator($array);
        $array = new RecursiveIteratorIterator($array);
        foreach ($array as $key => $value) {
            $newArray[] = $toLower ? strtolower($value) : $value;
        }
        return $newArray;
    }
    
    static public function objectToArray($object)
    {
        $returnArray = array();
        foreach ($object as $key => $value) {
            $returnArray[$key] = $value;
        }
        return $returnArray;
    }
    
    static public function extractElement($key, &$array)
    {
        if (array_key_exists($key, $array)) {
            $value = $array[$key];
            unset($array[$key]);
            return $value;
        }
    }
}