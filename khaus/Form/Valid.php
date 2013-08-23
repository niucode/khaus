<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Form
 * @version     1:20121108
 */

class Khaus_Form_Valid
{
    private $_vars = array();

    private $_errors = array();
    
    private $_requiredValues = array();
    
    const VALIDATE_RUT = 1;
    const VALIDATE_DATE = 2;
    const VALIDATE_EMAIL = 4;
    const VALIDATE_URL = 8;

    public function __construct(array $method)
    {
        foreach ($method as $key => $value) {
            $this->_vars[$key] = $value;
        }
    }

    public function __call($name, $arguments)
    {
        if (isset($this->_vars[$name])) {
            if (!isset($this->_errors[$name])) {
                if (!empty($this->_requiredValues) 
                 && !in_array($name, $this->_requiredValues) 
                 && (empty($this->_vars[$name]) && $this->_vars[$name] != '0')) {
                } else {
                    list($validation, $errorMessage) = $arguments;
                    if (is_string($validation)) {
                        if (!preg_match($validation, $this->_vars[$name])) {
                            $this->_errors[$name] = $errorMessage;
                        }
                    }
                    if (is_callable($validation)) {
                        if (!$validation($this->_vars[$name])) {
                            $this->_errors[$name] = $errorMessage;
                        }
                    }
                    if (is_int($validation)) {
                        if (($validation & self::VALIDATE_RUT) != 0) {
                            if (!$this->_validateRUT($this->_vars[$name])) {
                                $this->_errors[$name] = $errorMessage;
                            }
                        }
                        if (($validation & self::VALIDATE_DATE) != 0) {
                            if (!$this->_validateDate($this->_vars[$name])) {
                                $this->_errors[$name] = $errorMessage;
                            }
                        }
                        if (($validation & self::VALIDATE_EMAIL) != 0) {
                            if (!$this->_validateEmail($this->_vars[$name])) {
                                $this->_errors[$name] = $errorMessage;
                            }
                        }
                        if (($validation & self::VALIDATE_URL) != 0) {
                            if (!$this->_validateUrl($this->_vars[$name])) {
                                $this->_errors[$name] = $errorMessage;
                            }
                        }
                    }
                    if (is_array($validation)) {
                        list($min, $max) = $validation;
                        $varlen = strlen($this->_vars[$name]);
                        if ($varlen < $min || $varlen > $max) {
                            $this->_errors[$name] = $errorMessage;
                        }
                    }
                }
            }
        } else {
            $message = sprintf('%s is not found in form data', $name);
            throw new Khaus_Form_Exception($message, 100);
        }
    }
    
    public function required($name, $_ = '')
    {
        $arguments = Khaus_Helper_Array::arrayFlatten(func_get_args());
        $this->_requiredValues = $arguments;
        foreach ($arguments as $value) {
            $this->$value('/.+/i', 'El campo es obligatorio');
        }
    }
    
    private function _validateRUT($element)
    {
        $rut = str_replace(array('.', '-'), '', $element);
        if (preg_match('/^(\d{1,8})(\d|k|K)$/', $rut, $group)){
            $acum = 1;
            $rut = $group[1];
            for ($m = 0; $rut != 0; $rut /= 10) {
                $acum = ($acum + $rut % 10 * (9 - $m++ % 6)) % 11;
            }
            return chr($acum ? $acum + 47 : 75) == strtoupper($group[2]);
        }
    }
    
    private function _validateDate($element)
    {
        return strtotime($element);
    }
    
    private function _validateEmail($element)
    {
        return preg_match('/^[a-z0-9._-]+@(?:[a-z0-9-]+\.)+[a-z]{2,6}$/i', $element);
    }
    
    private function _validateUrl($element)
    {
        return preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6}).*\/?$/i', $element);
    }
    
    public function isEmpty()
    {
        return empty($this->_vars);
    }
    
    public function exist($name, $_ = '')
    {
        $arguments = Khaus_Helper_Array::arrayFlatten(func_get_args());
        foreach ($arguments as $value) {
            if (!isset($this->_vars[$value])) {
                return false;
            }
        }
        return true;
    }

    public function getFormData()
    {
        return $this->_vars;
    }

    public function isValid()
    {
        return empty($this->_errors);
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function printJsonSuccess($message)
    {
        $json = array();
        $json['khausFormResponse']['alert-success'] = $message;
        echo json_encode((object) $json);
        exit;
    }
    
    public function printJsonWarning($message)
    {
        $json = array();
        $json['khausFormResponse']['alert-warning'] = $message;
        echo json_encode((object) $json);
        exit;
    }

    public function printJsonDanger($message)
    {
        $json = array();
        $json['khausFormResponse']['alert-danger'] = $message;
        echo json_encode((object) $json);
        exit;
    }

    public function printJsonInfo($message)
    {
        $json = array();
        $json['khausFormResponse']['alert-info'] = $message;
        echo json_encode((object) $json);
        exit;
    }

    public function printJsonFormErrors()
    {
        $json = array();
        $json['khausFormResponse']['form-errors'] = $this->getErrors();
        echo json_encode((object) $json);
        exit;
    }

    public function formLocation($location)
    {
        $json = array();
        $json['khausFormResponse']['form-location'] = $location;
        echo json_encode((object) $json);
        exit;
    }

    public function __get($name)
    {
        if (isset($this->_vars[$name])) {
            return $this->_vars[$name];
        } else {
            $message = sprintf('%s is not found in form data', $name);
            throw new Khaus_Form_Exception($message, 110);
        }
    }
}