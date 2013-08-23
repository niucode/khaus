<?php
/**
 * Khaus Framework (https://github.com/niucode/khaus)
 *
 * @link https://github.com/niucode/khaus for the canonical source repository
 * @copyright Copyright (c) 2013 rhyudek1. (https://github.com/niucode/khaus)
 * @license https://github.com/niucode/khaus New BSD License
 *
 * @category    Khaus
 * @package     Khaus_Helper
 * @version     1:20120823
 */

class Khaus_Helper_Seo
{
    static public function removeAccents($string, $exceptions = '', $charset = 'UTF-8')
    {
        $html = get_html_translation_table(HTML_ENTITIES);
        $html = array_slice($html, 32, -4);
        if (is_string($exceptions)) {
            $exceptions = str_split($exceptions);
            foreach ($exceptions as $character) {
                unset($html[$character]);
            }
        }
        foreach ($html as $char => $entitie) {
            $bar[] = substr($entitie, 1, 1);
        }
        $string = htmlentities($string, ENT_QUOTES, $charset);
        $string = str_replace($html, $bar, $string);
        return $string;
    }
    static public function safeName($string)
    {
        $string = self::removeAccents($string);
        $string = strtolower($string);
        $string = preg_replace('/(&[a-z0-9]+;|[^a-z0-9_])/', '_', $string);
        $string = preg_replace('/[\s_]+/', '_', $string);
        $string = trim($string, '_');
        return $string;
    }
}