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
 * @version     3:20130719
 */
class Khaus_Helper_Integer
{
    public static function num2word($numero)
    {
        $positivoNegativo = '';
        $palabraFinal = '';
        $arrayFinal = array();
        $arrayConjuntos = array(4 => 'mil', 7 => 'millones', 13 => 'billones');
        $arrayPalabras = array(
            array('un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'),
            array('diez', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'),
            array('ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'),
        );
        if (is_numeric($numero)) {
            if ($numero == 0) {
                $palabraFinal = 'cero';
            } else {
                if ($numero < 0) {
                    $positivoNegativo = 'menos ';
                    $numero = $numero * -1;
                }
                $numero = (string) $numero;
                for ($o = 0, $i = strlen($numero) - 1; $i >= 0; $i--) {
                    $conector = '';
                    $numeroActual = $numero{$i} - 1;
                    $udc = $o++ % 3;
                    if (isset($arrayConjuntos[$o])) {
                        $conector = ' ' . $arrayConjuntos[$o];
                    }
                    if ($numeroActual > -1) {
                        if ($udc == 1 && $numero{$i + 1} > 0) {
                            $conector = ' y';
                        }
                        $arrayFinal[] = $arrayPalabras[$udc][$numeroActual] . $conector;
                    } else {
                        if (!empty($conector)) {
                            $arrayFinal[] = $conector;
                        }
                    }
                }
                $arrayFinal = array_reverse($arrayFinal);
                $palabraFinal = $positivoNegativo . implode(' ', $arrayFinal);
                $palabraFinal = preg_replace('/ +/', ' ', $palabraFinal);
                $reemplazos = array(
                    'diez y un'       => 'once',
                    'diez y dos'      => 'doce',
                    'diez y tres'     => 'trece',
                    'diez y cuatro'   => 'catorce',
                    'diez y cinco'    => 'quince',
                    'diez y seis'     => 'dieciseis',
                    'diez y siete'    => 'diecisiete',
                    'diez y ocho'     => 'dieciocho',
                    'diez y nueve'    => 'diecinueve',
                    'veinte y un'     => 'veintiun',
                    'veinte y dos'    => 'veintidos',
                    'veinte y tres'   => 'veintitres',
                    'veinte y cuatro' => 'veinticuatro',
                    'veinte y cinco'  => 'veinticinco',
                    'veinte y seis'   => 'veintiseis',
                    'veinte y siete'  => 'veintisiete',
                    'veinte y ocho'   => 'veintiocho',
                    'veinte y nueve'  => 'veintinueve',
                    'ciento mil'      => 'cien mil',
                    'un millones'     => 'un millon',
                    'y un millon'     => 'y un millones',
                    'millones mil'    => 'millones',
                );
                $palabraFinal = str_replace(array_keys($reemplazos), $reemplazos, $palabraFinal);
                $palabraFinal = preg_replace('/^un mil[^a-z]/s', 'mil ', $palabraFinal);
                $palabraFinal = preg_replace('/ciento$/s', 'cien', $palabraFinal);
                $palabraFinal = trim($palabraFinal);
            }
            return $palabraFinal;
        }
    }
}