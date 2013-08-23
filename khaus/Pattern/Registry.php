<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Pattern
 * @version     1:20120822
 */

class Khaus_Pattern_Registry
{
    static private $_registry = array();
    static private $_locked = array();

    /**
     * Verifica la existencia de un registro pasado por el argumento
     * retornando un valor booleano
     *
     * @param string $name
     * @throws si el argumento no es del tipo string
     * @return boolean
     */
    static public function exists($name)
    {
        if (is_string($name)) {
            return isset(self::$_registry[$name]);
        } else {
            throw new Khaus_Pattern_Exception('El argumento debe ser un string');
        }
    }

    /**
     * Verifica si el elemento esta definido como bloqueado
     *
     * @param string $name
     * @throws si el argumento no es del tipo string
     * @return boolean
     */
    static public function isLocked($name)
    {
        if (is_string($name)) {
            return in_array($name, self::$_locked);
        } else {
            throw new Khaus_Pattern_Exception('El argumento debe ser un string');
        }
        
    }
    
    /**
     * Agrega un nuevo elemento al registro
     * con $lock activado, el elemento se mantendra vigente
     * y no se podra borrar ni modificar.
     * Pueden registrarse cualquier tipo de datos, incluyendo objetos
     * Khaus_Pattern_Registry::add('nombre', new Object());
     *
     * @param string $name
     * @param mixed $item
     * @param boolean $lock [optional]
     * @throws si el primer argumento no es string
     * @throws si el elemento ya se encuentra registrado
     */
    static public function add($name, $item, $lock = false)
    {
        if (is_string($name)) {
            if (!self::exists($name)) {
                if ($lock) {
                    self::$_locked[] = $name;
                }
                self::$_registry[$name] = $item;
            } else {
                throw new Khaus_Pattern_Exception("El elemento $name ya se encuentra registrado");
            }
        } else {
            throw new Khaus_Pattern_Exception('El argumento $name debe ser un string');
        }
    }

    /**
     * Agrega un nuevo elemento al registro
     * si este ya existe lo sobrepone
     * con $lock activado, el elemento se mantendra vigente
     * y no se podra borrar ni modificar.
     * Pueden registrarse cualquier tipo de datos, incluyendo objetos
     * Khaus_Pattern_Registry::add('nombre', new Object());
     *
     * @param string $name
     * @param mixed $item
     * @param boolean $lock [optional]
     * @throws si el primer argumento no es string
     * @throws si el elemento ya se encuentra registrado
     */
    static public function forceAdd($name, $item, $lock = false)
    {
        if (is_string($name)) {
            if ($lock) {
                self::$_locked[] = $name;
            }
            if (!self::exists($name)) {
                self::$_registry[$name] = $item;
            } else {
                self::replace($name, $item);
            }
        } else {
            throw new Khaus_Pattern_Exception('El argumento $name debe ser un string');
        }
    }

    /**
     * Reemplaza el contenido de un elemento actualmente registrado
     *
     * @param string $name
     * @param mixed $item
     * @throws si el primer argumento no es string
     * @throws si el elemento no se encuentra registrado
     */
    static public function replace($name, $item)
    {
        if (is_string($name)) {
            if (self::exists($name) && !self::isLocked($name)) {
                self::$_registry[$name] = $item;
            } else {
                throw new Khaus_Pattern_Exception("El elemento no se encuentra registrado o esta bloqueado");
            }
        } else {
            throw new Khaus_Pattern_Exception('El argumento $name debe ser un string');
        }
    }

    /**
     * Retorna el elemento si se encuentra registrado
     *
     * <p>ELEMENTOS PERMANENTES:</p>
     * Existen elementos registrados por la configuracion del sistema
     * 
     * Khaus_Pattern_Registry::get('CONFIG_DATABASE');
     * Khaus_Pattern_Registry::get('CONFIG_APPLICATION');
     *
     * @param string $name
     * @throws si el argumento no es string
     * @throws si el elemento no se encuentra registrado
     * @return mixed
     */
    static public function get($name)
    {
        if (is_string($name)) {
            if (self::exists($name)) {
                return self::$_registry[$name];
            } else {
                throw new Khaus_Pattern_Exception('El elemento no se encuentra registrado');
            }
        } else {
            throw new Khaus_Pattern_Exception('El argumento debe ser del tipo string');
        }
    }

    /**
     * Elimina un elemento registrado de la lista
     *
     * @param string $name
     * @throws si el argumento no es string
     * @throws si el elemento no se encuentra registrado
     */
    static public function remove($name)
    {
        if (is_string($name)) {
            if (self::exists($name) && !self::isLocked($name)) {
                unset(self::$_registry[$name]);
            } else {
                throw new Khaus_Pattern_Exception('El elemento no se encuentra registrado o esta bloqueado');
            }
        } else {
            throw new Khaus_Pattern_Exception('El argumento debe ser del tipo string');
        }
    }

    /**
     * Elimina los elementos registrados
     * Omite los elementos que se encuentran bloqueados
     */
    static public function clear()
    {
        foreach (self::$_registry as $name => $element) {
            if (!self::isLocked($name)) {
                unset(self::$_registry[$name]);
            }
        }
    }
}