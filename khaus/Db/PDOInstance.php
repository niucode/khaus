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

class Khaus_Db_PDOInstance extends PDO
{
    public function __construct($driver, $host, $database, $username, $password)
    {
        $dsn = "$driver:host=$host;dbname=$database";
        $instance = parent::__construct($dsn, $username, $password);
        
        parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $fetchResource = Khaus_Config::database('fetch_type');
        if ($fetchResource != null) {
            $fetchResource = 'PDO::' . $fetchResource;
            if (defined($fetchResource)) {
                parent::setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, constant($fetchResource));
            }
        }
        return $instance;
    }
}