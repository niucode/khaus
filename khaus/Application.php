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

class Khaus_Application
{
    public function config($iniFileLocation)
    {

        $ini = new Khaus_Core_Ini();
        $options = $ini->loadIniFile($iniFileLocation);
        $options = array_change_key_case($options, CASE_LOWER);
        if ($options['application']['timezone']) {
            date_default_timezone_set($options['application']['timezone']);
            ini_set('date.timezone', $options['application']['timezone']);
            setlocale(LC_ALL,'es_ES', 'es_ES', 'esp');
        }
        if (isset($options['application']['session_timelife'])) {
            $timelife = (int) $options['application']['session_timelife'];
            session_set_cookie_params($timelife, dirname($_SERVER['SCRIPT_NAME']));
            ini_set("session.gc_maxlifetime", $timelife);
            ini_set("session.cookie_lifetime", $timelife);
        }
        $options = json_decode(json_encode($options), false);
        Khaus_Pattern_Registry::add('CONFIG', $options, true);
        mb_internal_encoding('utf-8');
        session_start();
        return $this;
    }

    public function run()
    {
        try {
            $app = new Khaus_Controller();
            echo $app->getResponse();
        } catch (Exception $e) {
            if ((boolean) Khaus_Pattern_Registry::get('CONFIG')->application->development) {
                ob_end_clean();
                $error = new Khaus_Controller('error');
                $error->exception = $e;
                echo $error->getResponse();
            }
        }
    }
}