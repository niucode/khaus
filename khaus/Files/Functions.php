<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Files
 * @version     1:20120823
 */

class Khaus_Files_Functions
{
    static public function copy(Khaus_Files_Info $source, $destination, $overwrite = false)
    {
        // IF: El directorio ya existe
        if (is_dir($destination)) {
            // IF: El parametro de sobreescritura es true
            if ($overwrite) {
                self::remove($destination);
            } else {
                throw new Khaus_Files_Exception('El directorio de destino ya existe');
            }
        }
        // IF: Falla la creacion del directorio de destino
        if (!mkdir($destination, 0775, true)) {
            throw new Khaus_Files_Exception('Problemas con el directorio de destino');
        }
        $tree = $source->getTree();
        $location = $source->getLocation();
        foreach ($tree as $key => $value) {
            $tmpDirectory = $location . DIRECTORY_SEPARATOR . $value;
            $tmpDestination = $destination . DIRECTORY_SEPARATOR . $value;
            // IF: es un directorio
            if (is_dir($tmpDirectory)) {
                mkdir($tmpDestination);
            } else {
                copy($tmpDirectory, $tmpDestination);
            }
        }
    }
    
    static public function removeDir($directory)
    {
        if (is_dir($directory)) {
            $directory = rtrim($directory, '\\/');
            foreach (glob($directory . '/*') as $value) {
                if (is_dir($value)) {
                    self::removeDir($value);
                } else {
                    unlink($value);
                }
            }
            rmdir($directory);
        }
    }
}