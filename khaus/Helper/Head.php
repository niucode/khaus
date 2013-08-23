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

class Khaus_Helper_Head
{

    /**
     * Obtiene la URL base del script
     *
     * Captura la ruta donde se esta ejecutando el script
     * desde la informacion de entorno de servidor
     * luego se le da el formato especifico para ser usada
     * en la etiqueta <base />
     *
     * @return string
     */
    static public function basename()
    {
        $base = $_SERVER['SCRIPT_NAME'];
        $base = dirname($base);
        $base = str_replace('\\', '/', $base);
        $base.= substr($base, -1) != '/' ? '/' : '';
        return $base;
    }
}