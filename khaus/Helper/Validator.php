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

    public static function rut($rut)
    {
        $rut = str_replace(array('.', '-'), '', $rut);
        if (preg_match('/^([0-9]{1,8})([0-9]|k|K)$/', $rut, $group)){
            $acum = 1;
            $rut = $group[1];
            for ($m = 0; $rut != 0; $rut /= 10) {
                $acum = ($acum + $rut % 10 * (9 - $m++ % 6)) % 11;
            }
            return chr($acum ? $acum + 47 : 75) == strtoupper($group[2]);
        }
        return false;
    }

    public static function datetime($datetime)
    {
        return strtotime($datetime) !== false;
    }

    public static function email($mail)
    {
        return preg_match('/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $mail);
    }

    public static function uri($uri)
    {
        filter_var($uri, FILTER_VALIDATE_URL);
    }

    public static function numeric($numeric)
    {
        return is_numeric($numeric);
    }

    public static function numericPositive($numeric)
    {
        return preg_match('/^[0-9]+$/', $numeric);
    }
}