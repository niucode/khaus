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

class Khaus_Files_Zipper
{
    private $_zip;
    
    public function __construct($filename)
    {
        $this->_zip = new ZipArchive();
        if ($this->_zip->open($filename, ZIPARCHIVE::CREATE) !== true) {
            $message = sprintf('Imposible crear el archivo (%s)', $filename);
            throw new Khaus_Files_Exception($message);
        }
    }
    
    public function zip($elements, $rootPath = '')
    {
        if ($rootPath !== '' && is_string($rootPath)) {
            chdir($rootPath);
        }
        if ($elements instanceof Khaus_Files_Info) {
            foreach ($elements->getTree() as $file) {
                if (is_dir($file)) {
                    $this->_zip->addEmptyDir($file);
                }
                if (is_file($file)) {
                    $this->_zip->addFile($file);
                }
            }
        } else if (is_array($elements)) {
            foreach ($elements as $filename) {
                $filename = $filename;
                switch (true) {
                    case is_file($filename):
                        $this->_zip->addFile($filename);
                        break;
                    case is_dir($filename):
                        $elements = new Khaus_Files_Info($filename);
                        $elements = $elements->getTree();
                        foreach ($elements as $file) {
                            if (is_dir($file)) {
                                $this->_zip->addEmptyDir($file);
                            }
                            if (is_file($file)) {
                                $this->_zip->addFile($file);
                            }
                        }
                        break;
                }
            }
        }
    }
    
    public function close()
    {
        $this->_zip->close();
    }
}