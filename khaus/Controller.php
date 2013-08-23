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

class Khaus_Controller
{
    private $_className;
    private $_methodName;
    private $_response;
    private $_variables;
    
    private $_khausMode;

    public function __construct($className = '', $methodName = '')
    {
        $this->_variables = array();
        $this->_khausMode = false;
        if (empty($className) && empty($methodName)) {
            $request = new Khaus_Core_Request();
            list ($controller, $action) = $request->getRequest();
            // si hay una llamada a khaus y esta activado el modo development
            if (strtolower($controller) == 'khaus' && (boolean) Khaus_Config::application('development')) {
                new Khaus_Khaus_Admin();
                $this->_khausMode = true;
                $this->_response = '';
            } else {
                $this->_className = $this->_getClassName($controller);
                $this->_methodName = $this->_getMethodName($action);
            }
        } else {
            $this->_className = $this->_getClassName($className);
            $this->_methodName = $this->_getMethodName($methodName);
        }
    }
    
    public function __set($name, $value) {
        $this->_variables[$name] = $value;
    }

    private function _makeResponse()
    {
        // $classLocation: ruta a la clase correspondiente
        $classLocation = sprintf('%s/controllers/%s.php', APPLICATION_PATH, $this->_className);
        // IF: la ruta de la class existe
        if (is_file($classLocation)) {
            require_once $classLocation; // incluyo el archivo con la class
            $class = new $this->_className(); // instancio la class
            foreach ($this->_variables as $key => $value) { // recorro las variables asignadas y las evaluo
                $class->$key = $value;
            }
            $controllerName = strtolower(str_replace('Controller', '', $this->_className));
            $actionName = strtolower(str_replace('Action', '', $this->_methodName));
            
            if (method_exists($class, 'init')) $class->init(); // ejecuto el metodo init() si existe
            if (method_exists($class, $this->_methodName)) { // si existe el action como metodo, lo asigno
                $finalMethodName = $this->_methodName;
                $class->setControllerName($controllerName);
                $class->setActionName($actionName);
            } else {
                if ($class->getActionCapture() != null) { // si existe un redireccionamiento de action ejecutar este
                    $finalMethodName = $this->_getMethodName($class->getActionCapture());
                    $class = new $this->_className($finalMethodName);
                    $class->setControllerName($controllerName);
                    $class->setActionName($actionName);
                } else { // si no existe el action, envio exception 404
                    $method = str_replace('Action', '', $this->_methodName);
                    $message = "Action $method no encontrada";
                    throw new Khaus_Exception($message, 404);
                }
            }
            $class->{$finalMethodName}();
            $this->_response = $class->response();
        } else {
            $class = str_replace('Controller', '', $this->_className);
            $message = "Controller $class no encontrado";
            throw new Khaus_Exception($message, 404);
        }
    }

    public function getResponse()
    {
        if (!$this->_khausMode) {
            $this->_makeResponse();
        }
        return $this->_response;
    }

    private function _getClassName($className = "")
    {
        if (empty($className)) {
            $className = Khaus_Config::application('controller');
        }
        $className = strtolower($className);
        $className = ucfirst($className);
        $className = sprintf('%sController', $className);
        return (string) $className;
    }

    private function _getMethodName($methodName = "")
    {
        if (empty($methodName)) {
            $methodName = $this->_className;
            $methodName = str_replace('Controller', '', $methodName);
        }
        $methodName = strtolower($methodName);
        $methodName.= 'Action';
        return (string) $methodName;
    }
}