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

class Khaus_Helper_Time
{
    /**
     * Transforma los segundos a formato cronometro hh:mm:ss
     * 
     * Como parametro acepta valores integer entre 0 y 359.999
     * los numeros negativos seran pasados a positivos con la funcion abs()
     * si el valor es mayor a 359999, el resultado sera el maximo posible
     * en este caso 99:99:99
     *
     * @param integer $seconds segundos a transformar
     * @return string
     */
    public static function chronoTime($seconds)
    {
        $s = round(abs((integer) $seconds));
        if ($seconds < 360000) {
            $h = floor($s / 3600);
            $m = floor($s % 3600 / 60);
            return sprintf('%02d:%02d:%02d', $h, $m, $s % 60);
        } else {
            return '99:99:99';
        }
    }

    public static function elapsedTime($datetime, $language = 'es')
    {
        $elapsed = time() - strtotime($datetime);
        $lapsus = array(60, 60, 24, 30, 12);
        $text = array(
            'es' => array(
                array(' segundo', ' segundos'),
                array(' minuto', ' minutos'),
                array(' hora', ' horas'),
                array(' d&iacute;a', ' d&iacute;as'),
                array(' mes', ' meses'),
                array(' a&ntidle;o', ' a&ntidle;os'),
            ),
            'en' => array(
                array(' second', ' seconds'),
                array(' minute', ' minutes'),
                array(' hour', ' hours'),
                array(' day', ' days'),
                array(' month', ' months'),
                array(' year', ' years'),
            ),
        );
        foreach ($lapsus as $key => $div) {
            if ($elapsed < $div) {
                $time = $elapsed . ($elapsed == 1 ? $text[$language][$key][0] : $text[$language][$key][1]);
                break;
            } else {
                $elapsed = floor($elapsed / $div);
            }
        }
        return $time;
    } 
}
