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

class Khaus_Helper_Validator
{
    public static function mail($string, $caseInsensitive = true, $error = '')
    {
        $ci = $caseInsensitive ? 'i' : '';
        $pattern = sprintf('/^[\w-.]{1,20}@[\w-.]{1,30}\.([a-z]{1,6}){1,5}$/%s', $ci);
        if (!preg_match($pattern, $string)) {
            if (empty($error)) {
                $errorMessage = 'Email invalido';
            }
            throw new Khaus_Helper_Exception($errorMessage);
        }
        return true;
    }
    
    public static function char($string, $min, $max, $caseInsensitive = true, $error = '')
    {
        $ci = $caseInsensitive ? 'i' : '';
        $pattern = sprintf('/^[a-z]{%d,%d}$/%s', $min, $max, $ci);
        if (!preg_match($pattern, $string)) {
            if (empty($error)) {
                $errorMessage = "Solo se permiten de $min a $max caracteres";
            }
            throw new Khaus_Helper_Exception($errorMessage);
        }
        return true;
    }
    
    public static function digit($digits, $min, $max, $error = '')
    {
        $pattern = sprintf('/^[0-9]{%d,%d}$/', $min, $max);
        if (!preg_match($pattern, $string)) {
            if (empty($error)) {
                $errorMessage = "Solo se permiten numeros hasta $max caracteres de longitud";
            }
            throw new Khaus_Helper_Exception($errorMessage);
        }
        return true;
    }
    
    public static function alnum($string, $min, $max, $caseInsensitive = true, $error = '')
    {
        $ci = $caseInsensitive ? 'i' : '';
        $pattern = sprintf('/^[a-z0-9]{%d,%d}$/%s', $min, $max, $ci);
        if (!preg_match($pattern, $string)) {
            if (empty($error)) {
                $errorMessage = "Solo se permiten de $min a $max caracteres alfanumericos";
            }
            throw new Khaus_Helper_Exception($errorMessage);
        }
        return true;
    }
}