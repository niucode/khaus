<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Dom
 * @version     1:20120823
 */

class Khaus_Dom_HTMLXpath extends DOMXPath
{
    private $_uri;
    
    public function __construct($location)
    {
        $this->_uri = Khaus_Helper_Html::uriSchemeCheck($location);
        @parent::__construct(DOMDocument::loadHTMLFile($this->_uri));
    }
    
    public function meta($metaname, $caseInsensitive = false)
    {
        if ($caseInsensitive) {
            $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $lower = 'abcdefghijklmnopqrstuvwxyz';
            $metaname = strtolower($metaname);
            $query = sprintf('//meta[@name=contains("%s", translate(@name, "%s", "%s"))]', $metaname, $upper, $lower);
        } else {
            $query = sprintf('//meta[@name="%s"]', $metaname);
        }
        $elements = $this->query($query);
        if ($elements->length > 0) {
            return $elements->item(0)->attributes->getNamedItem('content')->nodeValue;
        }
        return false;
    }
    
    public function title()
    {
        $element = $this->query('//title');
        return $element->length > 0 ? $element->item(0)->nodeValue : false;
    }
}