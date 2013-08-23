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

class Khaus_Core_Layout
{
    private $_template;
    public $params;
    public $view;
    
    public function __construct()
    {
        $this->params = new stdClass();
        $layoutTemplatePath = APPLICATION_PATH . '/layouts/layout.phtml';
        $this->_template = new Khaus_Core_Template($layoutTemplatePath);
        $this->view = $this->_template->view;
    }
    
    public function render()
    {
        foreach ($this->params as $key => $value) {
            $this->view->$key = $value;
        }
        return $this->_template->render();
    }
}