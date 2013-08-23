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
 * @version     1:20130307
 */

class Khaus_Images_Adjust
{
    private $_imageLocation;
    
    private $_imageExtension;
    
    private $_allowed;
    
    private $_quality;
    
    private $_returnBase64;
    
    private $_saveImageLocation;
    
    private $_imageResource;
    
    public function __construct($url)
    {
        $this->_imageLocation = $url;
        $this->_imageExtension = null;
        $this->_allowed = array('jpg', 'jpeg', 'png', 'gif');
        $this->_quality = 70;
        $this->_returnBase64 = false;
        $this->_saveImageLocation = false;
        $this->_imageResource = null;
    }
    
    public function setAllowedTypes($types = array())
    {
        $this->_allowed = $types;
    }
    
    public function setQuality($quality)
    {
        $this->_quality = $quality;
    }
    
    public function setReturnBase64($returnBase64)
    {
        $this->_returnBase64 = $returnBase64;
    }
    
    public function setSaveImageLocation($saveImageLocation)
    {
        $this->_saveImageLocation = $saveImageLocation;
    }

    public function isImage()
    {
        if (is_null($this->_imageExtension)) {
            $headers = new Khaus_Http_Uri($this->_imageLocation);
            $headers = $headers->getHeaders();
            $contentType = str_replace('image/', '', $headers['content_type']);
            if (in_array($contentType, $this->_allowed)) {
                $this->_imageExtension = $contentType;
                return true;
            }
            $this->_imageExtension = false;
            return false;
        }
        return !!$this->_imageExtension;
    }
    
    public function adjustToBox($width, $height)
    {
        if ($this->isImage()) {
            $image = $this->_imageCreate();
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);
            $dif_percent_width = $originalWidth * 100 / $width;
            $dif_percent_height = $originalHeight * 100 /$height;
            if (($dif_percent_height > $dif_percent_width) || ($originalWidth < $width && $originalHeight > $height)) {
                $n_height = $originalHeight * $width / $originalWidth;
                $n_width = $width;
                $axis = 'y';
                $d_width = $n_width + ($n_width - $width);
                $d_height = $n_height + ($n_height - $height);
            } else if (($dif_percent_width > $dif_percent_height) || ($originalWidth > $width && $originalHeight < $height)) {
                $n_height = $height;
                $n_width = $originalWidth * $height / $originalHeight;
                $axis = 'x';
                $d_width = $n_width + ($n_width - $width);
                $d_height = $n_height + ($n_height - $height);//$height;
            } else if ($dif_percent_width == $dif_percent_height) {
                $axis = null;
                $n_height = $d_height = $height;
                $n_width = $d_width = $width;
            }
            $newImage = imagecreatetruecolor($n_width, $n_height);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $n_width, $n_height, $originalWidth, $originalHeight);
            if ($this->_returnBase64) {
                ob_start();
                imagejpeg($newImage, null, $this->_quality);
                $imageData = ob_get_contents();
                ob_end_clean();
                return array(
                    'd_width'   => $d_width,
                    'd_height'  => $d_height,
                    'axis'      => $axis,
                    'image'     => base64_encode($imageData),
                    'width'     => $n_width,
                    'height'    => $n_height,
                );
            }
            return imagejpeg($newImage, $this->_saveImageLocation, $this->_quality);
        } else {
            throw new Khaus_Images_Exception('La url especificada no es una imagen valida');
        }
    }
    
    private function _imageCreate()
    {
        if (is_null($this->_imageResource)) {
            switch ($this->_imageExtension) {
                case 'jpeg':
                    $image = imagecreatefromjpeg($this->_imageLocation);
                    break;
                case 'png':
                    $image = imagecreatefrompng($this->_imageLocation);
                    break;
                case 'gif':
                    $image = imagecreatefromgif($this->_imageLocation);
                    break;
                default:
                    throw new Khaus_Images_Exception('Formato de imagen no agregado, no se puede crear');
                    break;
            }
            $this->_imageResource = $image;
            return $image;
        }
        return $this->_imageResource;
    }
}