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

class Khaus_Config
{

    private static $_config;
    
    private static function _configInstance()
    {
        if (!(self::$_config instanceof stdClass)) {
            self::$_config = Khaus_Pattern_Registry::get('CONFIG');
        }
        return self::$_config;
    }

    /**
     * Llamada a los datos de configuracion de aplicacion
     * 
     * @example:
     * Khaus_Config::application($name);<br />
     * 
     * En donde los valores posibles de $name pueden ser
     * 
     * <b>TITLE:</b>        Titulo de la aplicacion<br />
     * <b>CONTROLLER:</b>   Controlador por defecto de la applicacion<br />
     * <b>TIMEZONE:</b>     Zona horaria por defecto<br />
     * <b>DEVELOPMENT:</b>  Modo de desarrollo (boolean)<br />
     * 
     * @param string $name valor a acceder en la configuracion
     * @param mixed [optional]$value contenido si se desea modificar el valor
     * @return valor de configuracion
     */
    public static function application($name, $value = null)
    {
        $name = strtolower($name);
        if (!is_null($value)) {
            self::_configInstance()->application->$name = $value;
        }
        return self::_configInstance()->application->$name;
    }

    /**
     * Llamada a los datos de configuracion de la base de datos
     *
     * @example:
     * Khaus_Config::database($value);<br />
     *
     * En donde los valores posibles de $name pueden ser
     *
     * <b>DRIVER:</b>           Tipo de base de dato ej mysql, postgresql, etc<br />
     * <b>HOSTNAME:</b>         Direccion del servidor de la base de datos<br />
     * <b>USERNAME:</b>         Nombre de usuario de la base de datos<br />
     * <b>PASSWORD:</b>         Contrasena de la base de datos<br />
     * <b>DATABASE_NAME:</b>    Nombre de la base da datos<br />
     *
     * @param string $value valor a rescatar de la configuracion
     * @return valor de configuracion
     */
    public static function database($name)
    {
        if (Khaus_Pattern_Registry::exists('DB_NAME_TEMP')) {
            $database = Khaus_Pattern_Registry::get('DB_NAME_TEMP');
        } else {
            $database = self::application('database');
        }
        Khaus_Pattern_Registry::forceAdd('DB_FINAL_NAME', $database);
        $database .= ' : database';
        $name = strtolower($name);
        return self::_configInstance()->$database->$name;
    }
}