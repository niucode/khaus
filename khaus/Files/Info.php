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

class Khaus_Files_Info
{
    
    private $_location;
    
    private $_elements = array();
    
    private $_folders = array();
    
    private $_files = array();
    
    public function __construct($location, $recursive = false)
    {
        /*
        if (mb_detect_encoding($location, 'UTF-8, ISO-8859-1', true) != false) {
            $location = utf8_decode($location);
        }*/
        if (is_dir($location) || is_file($location)) {
            $location = rtrim($location, '\\/');
            $this->_location = $location;
            $this->_elements = $this->_makeTree($location, $recursive);
        } else {
            throw new Khaus_Files_Exception('Archivo o directorio no existente');
        }
    }
    
    public function getLocation()
    {
        return $this->_location;
    }
    
    public function getTree()
    {
        return $this->_elements;
    }
    
    public function getFolders()
    {
        return $this->_folders;
    }
    
    public function getFiles()
    {
        return $this->_files;
    }
    
    public function setRelative()
    {
        foreach ($this->_elements as $key => $value) {
            $value = str_replace($this->_location, '', $value);
            $value = ltrim($value, '\\/');
            $this->_elements[$key] = $value;
        }
        foreach ($this->_folders as $key => $value) {
            $value = str_replace($this->_location, '', $value);
            $value = ltrim($value, '\\/');
            $this->_folders[$key] = $value;
        }
        foreach ($this->_files as $key => $value) {
            $value = str_replace($this->_location, '', $value);
            $value = ltrim($value, '\\/');
            $this->_files[$key] = $value;
        }
    }
    
    public function setAbsolute()
    {
        foreach ($this->_elements as $key => $value) {
            $value = ltrim($value, '\\/');
            $value = $this->_location . DIRECTORY_SEPARATOR . $value;
            $this->_elements[$key] = $value;
        }
        foreach ($this->_folders as $key => $value) {
            $value = ltrim($value, '\\/');
            $value = $this->_location . DIRECTORY_SEPARATOR . $value;
            $this->_folders[$key] = $value;
        }
        foreach ($this->_files as $key => $value) {
            $value = ltrim($value, '\\/');
            $value = $this->_location . DIRECTORY_SEPARATOR . $value;
            $this->_files[$key] = $value;
        }
    }
    
    private function _makeTree($location, $recursive)
    {
        $items = glob($location . '/*');
        for ($i = 0; $i < count($items); $i++) {
            if (is_dir($items[$i])) {
                $this->_folders[] = $items[$i];
                if ($recursive) {
                    $add = glob($items[$i] . '/*');
                    $items = array_merge($items, $add);
                }
            } else {
                $this->_files[] = $items[$i];
            }
        }
        sort($items);
        return $items;
    }
    
    public function getFileType($extension)
    {
        if (empty($this->_elements)) {
            $this->_elements = $this->tree();
        }
        $return_files = array();
        foreach ($this->_files as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (strcasecmp($ext, $extension) == 0) {
                $return_files[] = $file;
            }
        }
        return $return_files;
    }
    
    private function _formatBytes($size)
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }
    
    public function getFilesWithInfo()
    {
        $return_files = array();
        foreach ($this->_files as $filename) {
            $fileLocation = $this->_location . '/' . $filename;
            $info = pathinfo($fileLocation);
            $return_files[] = array(
                'basename'  => utf8_encode($info['basename']),
                'filename'  => utf8_encode($info['filename']),
                'ext'       => strtolower($info['extension']),
                'size'      => $this->_formatBytes(filesize($fileLocation)),
                'time'      => fileatime($fileLocation),
                'type'      => '',
            );
        }
        return $return_files;
    }
}