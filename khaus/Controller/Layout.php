<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Controller
 * @version     1:20130111
 */

class Khaus_Controller_Layout
{
    public $layout;
    private $_actionName;
    private $_controllerName;
    private $_content;

    public function __construct()
    {
        $this->layout = new stdClass();
    }
    
    public function getActionName()
    {
        return $this->_actionName;
    }
    
    public function getControllerName()
    {
        return $this->_controllerName;
    }
    
    public function getContent()
    {
        return $this->_content;
    }
    
    public function setControllerName($controllerName)
    {
        $this->_controllerName = $controllerName;
    }

    public function setActionName($actionName)
    {
        $this->_actionName = $actionName;
    }

    public function setContent($content)
    {
        $this->_content = $content;
    }



    /**
     * Obtiene un string con el template de otro controlador
     * 
     * Este metodo no realiza la carga de variables ni la ejecucion 
     * del metodo en cuestion, solo retorna la vista sin procesar
     * 
     * @param String $controller Nombre del controlador
     * @param String $action Nombre del action
     * @return String
     */ 
    public function returnTemplate($controller, $action, array $parameters = array())
    {
        $templatePath   = '%s/views/%s/%s.phtml';
        $templatePath   = sprintf($templatePath, APPLICATION_PATH, $controller, $action);
        $template = new Khaus_Core_Template($templatePath);
        foreach ($parameters as $key => $value) {
            $template->$key = $value;
        }
        return $template->render();
    }
    
    /**
     * Redirecciona a otra pagina
     * 
     * Este metodo redirecciona utilizando rutas internas o externas
     * @example 
     * $this->location('controller/action'); // redireccion interna
     * $this->location('www.rhyudek1.net'); // redireccion externa
     * 
     * @param string $location
     */
    public function location($location)
    {
        $where = 'Location: ';
        if (!preg_match('@^(www\.|http\://|ftp\://)@i', $location)) {
            $documenturi = DOCUMENT_URI;
            if ($location == '/') {
                $where .= $documenturi;
            } else {
                if (preg_match('@^(/?)([a-z0-9_-]+)/?([a-z0-9_-]+)?/?([?#].*)?$@i', $location, $matches)) {
                    if ($matches[1] == '/' && $documenturi{strlen($documenturi) - 1} == '/') {
                        $documenturi = substr($documenturi, 0, -1);
                    } else if ($matches[1] != '/' && $documenturi{strlen($documenturi) - 1} != '/') {
                        $documenturi .= '/';
                    }
                    $where .= $documenturi;
                }
            }
        }
        $where .= (string) $location;
        header($where);
    }
    
    /**
     * Refresca la pagina actual
     * 
     * Mediante un envio de header se refresca la pagina 
     * por defecto se refresca inmediatamente, aunque se puede pasar
     * el numero de segundos de retraso a traves de su parametro
     * 
     * @param int $seconds segundos de retraso para refrescar la pagina
     */
    public function refresh($seconds = 0)
    {
        header('refresh:' . $seconds . ';url=' . $_SERVER['REQUEST_URI']);
    }

    /**
     * Obtiene el resultado de la llamada a un Action
     * 
     * Metodo que ejecuta la llamada a un Action, se pueden
     * entregar datos adicionales a la vista, los cuales en el caso
     * de ya existir, sobreescribiran a los datos ya asignados
     * 
     * @param string @controller Nombre del controlador
     * @param string @action Nombre del action
     * @param boolean @layout Utilizar o no el template del layout
     * @param array @views arreglo con los datos que se entregaran al Action
     * @return string
     */
    public final function returnAction($controller, $action, $layout = false, array $views = array())
    {
        $controller = strtolower($controller);
        $controller = ucfirst($controller);
        $action = strtolower($action);
        $controllerName = $controller . 'Controller';
        $actionName = $action . 'Action';
        require_once APPLICATION_PATH . '/controllers/' . $controllerName . '.php';
        $return = new $controllerName($action);
        $return->_useLayout($layout);
        $return->$actionName();
        foreach ($views as $key => $value) {
            $return->view->$key = $value;
        }
        $return->customTemplate($controller, $action);
        return $return->response();
    }

    /**
     * Retorna una instancia del patron activeRecord
     * 
     * @example
     * $usuarios = $this->activeRecord('usuarios');
     * echo $usuarios->filter('nombre = "hidek1"')->email; // rhyudek1@gmail.com
     * 
     * @param string $tableName
     * @return Khaus_Pattern_ActiveRecord
     */
    public function activeRecord($tableName)
    {
        return new Khaus_Pattern_ActiveRecord($tableName);
    }
    
    /**
     * Cambia la Base de datos en uso
     * 
     * La base de datos que se usa por defecto es la que esta definida
     * dentro del archivo application.ini, se puede modificar durante el flujo
     * del programa utilizando este metodo
     * Como parametro se debe entregar el nombre de la base de datos
     * previamente configurada en el archivo application.ini
     * @example
     * # Bases de datos existentes en el archivo application.ini
     * # [database1 : database] < default
     * # [database2 : database]
     * 
     * $this->changeDB('database2');
     * $usuarios->insert();
     * $this->changeDB('database1');
     * 
     * @param string $dbname
     */
    public function changeDB($dbname)
    {
        Khaus_Pattern_Registry::forceAdd('DB_NAME_TEMP', $dbname);
    }
}