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
 * @version     17:20130528
 */

class Khaus_Controller_Action
{
    public $view;
    public $layout;
    private $_actionCapture;
    private $_templatePath;
    private $_layoutEnable;
    private $_actionName;
    private $_controllerName;

    public function __construct()
    {
        $this->_actionCapture = null;
        $this->_layoutEnable = null;
        $this->view = new stdClass();
        $this->layout = new stdClass();
    }
    
    public function getActionName() {
        return $this->_actionName;
    }
    
    public function getControllerName() {
        return $this->_controllerName;
    }
    
    public function setControllerName($controllerName) {
        $this->_controllerName = $controllerName;
    }

    public function setActionName($actionName) {
        $this->_actionName = $actionName;
    }
    
    public function printJson($element)
    {
        echo json_encode($element);
        exit;
    }

    /**
     * Elimina la session actual
     *
     * Destruye los datos de session tanto en el servidor
     * como la cookie almacenada en el cliente 
     * en caso de existir
     * @example
     * @this->sessionLogout();
     */
    public function sessionLogout()
    {
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"]);
        }
        session_destroy();
    }
    
    /**
     * Cambia el template por defecto
     * 
     * Si este metodo no esta especificado el template por defecto se encontrara
     * en la ruta application/views/controller/action.phtml
     * Especificando los dos parametros se puede cambiar esta ruta para 
     * reutilizar un mismo template u otros fines.
     * 
     * @example
     * # url: www.khaus.com/ejemplo/template
     * # ruta template actual application/views/ejemplo/template.phtml
     * $this->customTemplate('nuevaruta', 'nuevoarchivo');
     * # ruta template custom application/views/nuevaruta/nuevoarchivo.phtml
     * 
     * tener en cuenta de que el archivo y la ruta deben existir para su correcto
     * funcionamiento en el sistema.
     * 
     * @param type $controller
     * @param type $action
     */
    public function customTemplate($controller, $action)
    {
        $templatePath   = '%s/views/%s/%s.phtml';
        $templatePath   = sprintf($templatePath, APPLICATION_PATH, $controller, $action);
        $this->_templatePath = $templatePath;
    }

    /**
     * Obtiene un string con el template de otro controlador
     * 
     * Este metodo no realiza la carga de variables ni la ejecucion 
     * del metodo en cuestion, solo retorna la vista sin procesar
     * 
     * @param string $controller Nombre del controlador
     * @param string $action Nombre del action
     * @return string
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
     * Captura todos los action de la URL con un metodo especifico
     * 
     * El action es la segunda parte de la URL www.web.com/controller/action
     * esta por lo general es fija, si se desea utilizar de forma variable
     * se debe ejecutar este metodo dentro del controller init() para hacer 
     * una captura de cualquier valor entregado hacia un controlador especifico
     * @example
     * url: www.khaus.com/revisar/archivo53
     * 
     * public function init() {
     *     $this->setActionCapture('archivos');
     * }
     * public function archivosAction()
     * {
     *     echo $this->getActionName(); // archivo53
     * }
     * 
     * @param string $actionName nombre del metodo que se ejecutara
     * @return Khaus_Controller_Action
     */
    public function setActionCapture($actionName)
    {
        $this->_actionCapture = $actionName;
        return $this;
    }
    
    /**
     * Retorna el valor de la variable actionCapture
     * 
     * Si no se ha especificado algun valor para esta variable con el metodo
     * setActionCapture, este metodo retornara un valor null
     * 
     * @return mixed string or null
     */
    public function getActionCapture()
    {
        return $this->_actionCapture;
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

    /**
     * Activa o desactiva el renderizado del Layout
     *
     * Por defecto el uso del layout viene activado, el uso de la deshabilitacion
     * puede requerirse para llamadas de peticiones Ajax o similares
     *
     * @param boolean $boolean
     */
    public function useLayout($boolean)
    {
        $this->_layoutEnable = (bool) $boolean;
    }
    
    /**
     * Activa o desactiva el renderizado del Layout
     *
     * ALERTA: Metodo deprecated, utilizar useLayout()
     * 
     * @param boolean $boolean
     * @deprecated since version 15
     */
    public function _useLayout($boolean)
    {
        $this->useLayout($boolean);
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
     * Retorna una instancia del Validador de Formularios
     * 
     * Para su uso dentro del action se debe pasar como parametro el arreglo
     * que recibe los datos del formulario, POST GET o REQUEST
     * 
     * @param array $method $_POST | $_GET | $_REQUEST
     * @return Khaus_Form_Valid
     */
    public function form(array $method)
    {
        return new Khaus_Form_Valid($method);
    }

    /**
     * Respuesta de la aplicacion
     *
     * Dependiendo de los metodos utilizados, responde con el contenido
     * correspondiente a cada accion vinculandolo con el template
     * especificado o por defecto.
     *
     * @return string contenido de la aplicacion
     */
    public function response(array $request = array())
    {
        if (!empty($request)) {
            $this->customTemplate($request);
        }
        // IF: no se especifico un template custom se usa el default
        if (empty($this->_templatePath)) {
            $this->_templateDefault();
        }
        $template = new Khaus_Core_Template($this->_templatePath);
        $template->view = $this->view;
        $content = $template->render();
        // IF: es una peticion ajax se establece por defecto el layout deshabilitado
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
         && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' 
         && is_null($this->_layoutEnable)) {
            $this->_useLayout(false);
        }
        // IF: el uso del layout esta activado
        if ($this->_layoutEnable === true || is_null($this->_layoutEnable)) {
            $layoutPath = APPLICATION_PATH . '/layouts/Layout.php';
            require_once $layoutPath;
            $layoutBootstrap = new Layout();
            $layoutBootstrap->setActionName($this->getActionName());
            $layoutBootstrap->setControllerName($this->getControllerName());
            $layoutBootstrap->setContent($content);
            $layoutBootstrap->boot();
            $layout = new Khaus_Core_Layout();
            $layout->params = (object) array_merge((array) $layoutBootstrap->layout, (array) $this->layout);
            return $layout->render();
        } else {
            return $content;
        }
    }
    
    /**
     * Construye la ruta al template por defecto del controlador
     *
     * Captura el nombre de la class hija y el metodo invocador
     * para buscar en la ruta construida el respectivo template
     * y asignarlo a la propiedad $this->_templatePath
     */
    private function _templateDefault()
    {
        $c = $this->getControllerName();
        $a = $this->getActionName();
        $this->_templatePath = sprintf('%s/views/%s/%s.phtml', APPLICATION_PATH, $c, $a);
    }
}