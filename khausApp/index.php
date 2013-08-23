<?php
/**
 * Khaus Framework (https://github.com/khausoft/khaus)
 *
 * @link https://github.com/khausoft/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/khausoft/khaus)
 * @license https://github.com/khausoft/khaus New BSD License
 */

/*------------------------------------*
 * CONFIGURACION DE RUTAS DEL SISTEMA
 *------------------------------------*/

// Ruta a la carpeta de aplicacion
$applicationPath    = 'application';

// Ruta al archivo Autoload.php dentro de la carpeta khaus
$autoloadPath       = '../khaus/Autoload.php';


/*------------------------------------*
 * NO MODIFICAR EL CODIGO DESDE AQUI
 *------------------------------------*/
require_once $autoloadPath;
list($protocol) = explode('/', $_SERVER['SERVER_PROTOCOL']);
$uri = sprintf('%s://%s%s', $protocol, $_SERVER['SERVER_NAME'], dirname($_SERVER['PHP_SELF']));

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/' . $applicationPath));

defined('PUBLIC_PATH')
    || define('PUBLIC_PATH', dirname(__FILE__));

defined('DOCUMENT_URI')
    || define ('DOCUMENT_URI', $uri);

$application = new Khaus_Application();
$application->config(APPLICATION_PATH . '/configs/application.ini')
            ->run();