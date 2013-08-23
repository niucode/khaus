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
 * @version     2:20120822
 */

class Khaus_Core_Request
{
    protected $_request;

    private $_requestNumber = 2;

    public function __construct()
    {
        $script = $_SERVER['SCRIPT_NAME'];
        $script = dirname($script);
        $request = $_SERVER['REQUEST_URI'];
        // recorta el string en caso de ser necesario
        if (strpos($request, $script) === 0 && !in_array($script, array('\\', '/'))) {
            $request = str_ireplace($script, '', $request);
        }
        // IF: extiste una peticion query string dentro del request
        if (isset($_SERVER['QUERY_STRING'])) {
            $query = sprintf('?%s', $_SERVER['QUERY_STRING']);
            $request = str_ireplace($query, '', $request);
        }
        // IF: la estructura del request es valida
        if (preg_match('&^(/[\w-]+)?(/[\w-]+)*/?$&m', $request)) {
            $request = Khaus_Helper_String::counTrim($request, '/', 1);
            $request = explode('/', $request, $this->_requestNumber);
            for ($i = 0; $i < $this->_requestNumber; $i++) {
                $value = !isset($request[$i]) ? '' : $request[$i];
                $finalRequest[$i] = $value;
            }
            $this->_request = $finalRequest;
        } else {
            throw new Khaus_Core_Exception('Error en el nombre de la pagina', 404);
        }
    }

    public function getRequest()
    {
        return $this->_request;
    }
}