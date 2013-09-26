<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Khaus
 * @version     1:20130109
 */

class Khaus_Khaus_Admin
{
    private $_controllerPath;
    private $_viewPath;

    public function __construct() {
        $this->_controllerPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
        $this->_viewPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        $form = new Khaus_Form_Valid($_POST);
        // IF: eliminar un action
        if ($form->exist('del_action', 'actionName', 'controllerName')) {
            if ($this->_deleteAction($form->controllerName, $form->actionName)) {
                header('location:khaus');
            }
        }
        
        // IF: agregar un action
        if ($form->exist('add_action', 'actionName', 'controllerName')) {
            if ($this->_addAction($form->controllerName, $form->actionName)) {
                header('location:khaus');
            }
        }
        
        // IF: agregar un controller
        if ($form->exist('add_controller', 'controllerName')) {
            if ($this->_addController($form->controllerName)) {
                header('location:khaus');
            }
        }
        
        // IF: eliminar un controller
        if ($form->exist('del_controller', 'controllerName')) {
            if ($this->_deleteController($form->controllerName)) {
                header('location:khaus');
            }
        }
        $controllers = $matches = $methodMatch = array();
        $files = new Khaus_Files_Info($this->_controllerPath);
        $files->setRelative();
        $controllersNames = $files->getFileType('php');
        foreach ($controllersNames as $value) {
            if (preg_match('/^(.{1,25})Controller\.php$/', $value, $matches)) {
                if ($matches[1] != 'Error') {
                    require_once $this->_controllerPath . $matches[0];
                    $className = $matches[1] . 'Controller';
                    $controller = new ReflectionClass($className);
                    $actions = array();
                    foreach ($controller->getMethods() as $method) {
                        if (preg_match('/^(.{1,25})Action$/', $method->name, $methodMatch)) {
                            if ($methodMatch[1] != 'return') {
                                $actions[] = $methodMatch[1];
                            }
                        }
                    }
                    $controllers[$matches[1]] = $actions;
                }
            }
        }
        
        $template = new Khaus_Core_Template(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'template.phtml');
        $template->view->controllers = $controllers;
        $template->view->projectName = Khaus_Config::application('title');
        $template->view->breakpoint = round(count($controllers) / 3);
        echo $template->render();
    }
    
    private function _deleteAction($controller, $action)
    {
        $filename = implode(DIRECTORY_SEPARATOR, array(APPLICATION_PATH, 'controllers', $controller . 'Controller.php'));
        require_once $filename;
        $reflection_controller = new ReflectionClass($controller . 'Controller');
        $reflection_action = $reflection_controller->getMethod($action . 'Action');
        $startLine = $reflection_action->getStartLine();
        $endLine = $reflection_action->getEndLine();
        $fileArray = file($filename);
        array_splice($fileArray, $startLine - 1, $endLine - $startLine + 1);
        $file = fopen($filename, 'w');
        if (fwrite($file, implode('', $fileArray)) !== false) {
            fclose($file);
            $template = $this->_viewPath . strtolower($controller) . DIRECTORY_SEPARATOR . strtolower($action) . '.phtml';
            if (unlink($template)) {
                return true;
            }
        }
        return false;
    }
    
    private function _addAction($controller, $action)
    {
        $filename = $this->_controllerPath . $controller . 'Controller.php';
        require_once $filename;
        $reflection_controller = new ReflectionClass($controller . 'Controller');
        $endLine = $reflection_controller->getEndLine();
        $fileArray = file($filename);
        $newAction = sprintf("\r\n    public function %sAction()\r\n    {\r\n        // put your code here !\r\n    }\r\n}", $action);
        $fileArray[$endLine - 1] = preg_replace('/}\s*$/', $newAction, $fileArray[$endLine - 1]);
        if ($file = fopen($filename, 'w')) {
            if (fwrite($file, implode('', $fileArray)) !== false) {
                fclose($file);
                $template = $this->_viewPath . strtolower($controller) . DIRECTORY_SEPARATOR . strtolower($action) . '.phtml';
                if (fopen($template, 'w') !== false) {
                    return true;
                }
            }
        }
        return false;
    }
    
    private function _addController($controller)
    {
        $actionName = strtolower($controller);
        $controllerName = ucfirst($actionName);
        
        $filename = $this->_controllerPath . $controllerName . 'Controller.php';
        $newController = sprintf("<?php\r\nclass %sController extends Khaus_Controller_Action\r\n{\r\n    public function init()\r\n    {\r\n        // put your code here !\r\n    }\r\n\r\n    public function %sAction()\r\n    {\r\n        // put your code here !\r\n    }\r\n}", $controllerName, $actionName);
        if ($file = fopen($filename, 'w')) {
            if (fwrite($file, $newController) !== false) {
                fclose($file);
                $tempPath = $this->_viewPath . strtolower($controller);
                if (mkdir($tempPath)) {
                    $template = $tempPath . DIRECTORY_SEPARATOR . strtolower($controller) . '.phtml';
                    if (fopen($template, 'w') !== false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    private function _deleteController($controller)
    {
        $controllerName = ucfirst(strtolower($controller));
        $filename = $this->_controllerPath . $controllerName . 'Controller.php';
        if (unlink($filename)) {
            $tempPath = $this->_viewPath . strtolower($controller);
            Khaus_Files_Functions::removeDir($tempPath);
            return true;
        }
        return false;
    }
}