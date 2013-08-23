<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Images
 * @version     1:20120823
 */

class Khaus_Images_Thumbnail
{ 
    private $_width = 100;
    
    private $_height = 100;
    
    private $_quality = 90;
    
    private $_keepRatio = true;
    
    private $_image = '';
    
    private $_fileName = '';
    
    public function __construct($imageLocation)
    {
        $this->_image = (string) $imageLocation;
    }
    
    public function thumbName($location)
    {
        $this->_fileName = (string) $location;
    }
    
    public function keepRatio($boolean)
    {
        $this->_keepRatio = (boolean) $boolean;
    }
    
    public function setSize($width, $height)
    {
        $this->_width = (int) $width;
        $this->_height = (int) $height;
    }
    
    public function setQuality($quality)
    {
        if ($quality < 101 && $quality > 0) {
            $this->_quality = (int) $quality;
        }
    }
    
    public function makeThumbnails()
    {
        $img = getimagesize($this->_image);
        list($width, $height, $mime) = array($img[0], $img[1], $img['mime']);
        switch ($mime) {
            case 'image/jpeg':
                $origin = imagecreatefromjpeg($this->_image);
                break;
            case 'image/gif':
                $origin = imagecreatefromgif($this->_image);
                break;
            case 'image/png':
                $origin = imagecreatefrompng($this->_image);
                break;
        }
        $thumb = imagecreatetruecolor($this->_width, $this->_height);
        $new_width = $this->_width;
        $new_height = $this->_height;
        if (!$this->_keepRatio) {
            $cut_w = $cut_h = 0;
        } else {
            $wRealPercent = $width * 100 / ($width + $height);
            $wThumbPercent = $this->_width * 100 / ($this->_width + $this->_height);
            if ($wRealPercent == $wThumbPercent) {
                $cut_w = $cut_h = 0;
            } else if ($wRealPercent < $wThumbPercent) {
                $cut_w = 0;
                $cut_h = $height * $this->_width / $width;
                $cut_h = $cut_h - $this->_height;
                $cut_h = $cut_h / 2;
                $new_height += $cut_h;
            } else {
                $cut_h = 0;
                $cut_w = $width * $this->_height / $height;
                $cut_w = $cut_w - $this->_width;
                $cut_w = $cut_w / 2;
                $new_width += $cut_w;
            }
        }
        imagecopyresampled($thumb, $origin, 0, 0, $cut_w, $cut_h, $new_width, $new_height, $width, $height);
        return imagejpeg($thumb, $this->_fileName, $this->_quality);
    }
}