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
 * @version     1:20130910
 */

class Khaus_Form_Valid
{
    private $_vars                  = array();
    private $_errors                = array();
    private $_requiredValues        = array();
    
    const VALIDATE_RUT              = 1;
    const VALIDATE_DATE             = 2;
    const VALIDATE_EMAIL            = 3;
    const VALIDATE_URL              = 4;
    const VALIDATE_NUMERIC          = 5;
    const VALIDATE_NUMERIC_POSITIVE = 6;

    public function __construct(array $method)
    {
        foreach ($method as $key => $value) {
            $this->_vars[$key] = $value;
        }
    }

    public function __call($name, $arguments)
    {
        if (isset($this->_vars[$name])) {
            // if: el nombre del parametro existe como valor en el formulario
            $element = $this->_vars[$name];
            if (!isset($this->_errors[$name])) {
                if (!empty($this->_requiredValues) 
                 && !in_array($name, $this->_requiredValues) 
                 && (empty($element) && $element != '0')) {
                } else {
                    list($validation, $errorMessage) = $arguments;

                    if (is_string($validation)) {
                        // si la validacion es un string se procesa como expresion regular
                        if (!preg_match($validation, $element)) {
                            $this->_errors[$name] = $errorMessage;
                        }

                    } else if (is_callable($validation)) {
                        // si la validacion es una funcion se ejecuta la funcion
                        if (!(boolean) $validation($element)) {
                            $this->_errors[$name] = $errorMessage;
                        }

                    } else if (is_int($validation)) {
                        // si la validacion es un integer
                        $valid = true;
                        switch ($validation) {
                            case self::VALIDATE_RUT:
                                $valid = Khaus_Helper_Validator::rut($element);
                                break;
                            case self::VALIDATE_DATE:
                                $valid = Khaus_Helper_Validator::datetime($element);
                                break;
                            case self::VALIDATE_EMAIL:
                                $valid = Khaus_Helper_Validator::email($element);
                                break;
                            case self::VALIDATE_URL:
                                $valid = Khaus_Helper_Validator::uri($element);
                                break;
                            case self::VALIDATE_NUMERIC:
                                $valid = Khaus_Helper_Validator::numeric($element);
                                break;
                            case self::VALIDATE_NUMERIC_POSITIVE:
                                $valid = Khaus_Helper_Validator::numericPositive($element);
                                break;
                        }
                        if (!$valid) {
                            $this->_errors[$name] = $errorMessage;
                        }
                    } else if (is_array($validation)) {
                        // si la validacion es un array @deprecated
                        list($min, $max) = $validation;
                        $varlen = strlen($element);
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
        $json['khausFormResponse']['success'] = $message;
        echo json_encode((object) $json);
        exit;
    }
    
    public function printJsonWarning($message)
    {
        $json = array();
        $json['khausFormResponse']['warning'] = $message;
        echo json_encode((object) $json);
        exit;
    }

    public function printJsonDanger($message)
    {
        $json = array();
        $json['khausFormResponse']['danger'] = $message;
        echo json_encode((object) $json);
        exit;
    }

    public function printJsonInfo($message)
    {
        $json = array();
        $json['khausFormResponse']['info'] = $message;
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