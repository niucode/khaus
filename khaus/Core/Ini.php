<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Core
 * @version     1:20120822
 */

class Khaus_Core_Ini
{
    
    private $_errors = null;

    /**
     * Carga el archivo INI del disco con parse_ini_file()
     * Usa un manejador de errores privado para el uso de excepciones
     * @see _loadFileErrorHandler
     *
     * @throws si no se especifica correctamente el nombre del archivo
     * @throws si hay problemas con la carga del .ini
     * @return array
     */
    public function loadIniFile($filename)
    {
        // IF: el parametro es del tipo string
        if (is_string($filename)) {
            // Warnings y errores suprimidos
            set_error_handler(array($this, '_loadFileErrorHandler'));
            $iniArray = parse_ini_file($filename, true);
            restore_error_handler();
            // IF: no ocurrio algun error durante la carga del fichero
            if ($this->_errors == null) {
                return $iniArray;
            } else {
                throw new Khaus_Core_Exception($this->_errors);
            }
        } else {
            $message = 'El nombre del archivo de configuracion es invalido';
            throw new Khaus_Core_Exception($message);
        }
    }

    /**
     * Acumula en $this->_errors los problemas
     * que se puedan presentar al cargar el fichero .ini
     *
     * @access private
     */
    private function _loadFileErrorHandler($errno, $errstr, $errfile, $errline)
    {
        // IF: no hay errores acumulados en la variable
        if ($this->_errors === null) {
            $this->_errors = $errstr;
        } else {
            $this->_errors .= PHP_EOL . $errstr;
        }
    }
}