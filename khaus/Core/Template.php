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
 * @version     1:20120823
 */

class Khaus_Core_Template
{
    public $view;
    private $_areParamsRequired = true;
    private $_filename;
    
    /**
     * Template Class: Plantillas dentro de la aplicacion (MVC)
     * 
     * Se debe especificar la ruta del template al constructor
     * los parametros se envian a travez de la propiedad publica $view
     * y desde la plantilla se les llama directamente con $this
     *
     * @example plantilla.phtml
     * <div><?php echo $this->variable; ?></div>
     *
     * @example index.php
     * $template = new Khaus_View_Template('plantilla.phtml');
     * $template->view->variable = 'datos';
     * echo $template->render();
     * 
     * @throws si la ruta es incorrecta
     * @param string $filename ruta del template
     */
    public function __construct($filename)
    {
        if (!is_file($filename)) {
            $message = sprintf('Template no encontrado /%s', $filename);
            throw new Khaus_Core_Exception($message, 100);
        }
        $this->view = new stdClass();
        $this->_filename = $filename;
    }
    
    /**
     * Establece el uso obligatorio o no de los parametros entregados
     *
     * Por defecto viene con esta opcion en TRUE
     * si este es el caso, al no entregar una variable que el 
     * template este requiriendo se generara una excepcion.
     * Si se establece esta valor con FALSE
     * las variables que el template solicite y no existan
     * seran omitidas.
     *
     * @access public
     * @param  boolean $boolean
     */
    public function paramsRequired($boolean) {
        $this->_areParamsRequired = (boolean) $boolean;
    }
    
    /**
     * Retorna las peticiones de variables de la plantilla
     * @example :
     *
     *      <?php echo $this->view->dato; ?>
     *      <?php echo $this->dato; ?>
     *
     * @param   string $name nombre del parametro
     * @throws  en caso de no existir el parametro
     * @return  string
     */
    public function __get($name)
    {
        if (!isset($this->view->$name)) {
            if ($this->_areParamsRequired) {
                $message = sprintf('Parametro no existente $this->%s', $name);
                throw new Khaus_Core_Exception($message, 100);
            } else {
                return NULL;
            }
        } else {
            return $this->view->$name;
        }
    }

    /**
     * Entrega el template procesado
     *
     * Mediante control de flujos captura la plantilla
     * asignandose las variables solicitadas al objeto mediante $this
     *
     * @throws si la plantilla no a sido asignada
     * @return string template procesado
     */
    public function render()
    {
        ob_start();
        include $this->_filename;
        $template = ob_get_contents();
        ob_end_clean();
        return $template;
    }
}