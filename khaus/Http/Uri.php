<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Http
 * @version     1:20130307
 */

class Khaus_Http_Uri
{
    private $_uri;
    
    private $_headers;
    
    public function __construct($uri)
    {
        $this->_uri = $uri;
        $this->_headers = null;
    }
    
    public function getHeaders()
    {
        if (is_null($this->_headers)) {
            $curl = curl_init($this->_uri);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $c = curl_exec($curl);
            $contentType = curl_getinfo($curl);
            curl_close($curl);
            $this->_headers = $contentType;
            return $this->_headers;
        }
        return $this->_headers;
    }
}