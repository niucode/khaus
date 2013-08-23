<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Db
 * @version     1:20120823
 */

class Khaus_Db_Connection
{
    public static function getInstance()
    {
        $reloadDB = false;
        if (Khaus_Pattern_Registry::exists('DB_NAME_TEMP')) {
            $temp = Khaus_Pattern_Registry::get('DB_NAME_TEMP');
            if (Khaus_Pattern_Registry::exists('DB_FINAL_NAME')) {
                if ($temp != Khaus_Pattern_Registry::get('DB_FINAL_NAME')) {
                    $reloadDB = true;
                }
            }
        }
        if (!Khaus_Pattern_Registry::exists('DB') || $reloadDB) {
            $conection = new Khaus_Db_PDOInstance(
                Khaus_Config::database('driver'),
                Khaus_Config::database('hostname'),
                Khaus_Config::database('dbname'),
                Khaus_Config::database('username'),
                Khaus_Config::database('password')
            );
            Khaus_Pattern_Registry::forceAdd('DB', $conection);
            $conection->query("SET NAMES 'utf8'");
            return $conection;
        } else {
            $conection = Khaus_Pattern_Registry::get('DB');
            $conection->query("SET NAMES 'utf8'");
            return $conection;
        }
    }
}