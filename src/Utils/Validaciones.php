<?php

namespace App\Utils;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Validaciones.
 *
 * @author ibilbao
 */
class Validaciones
{
    public static function validar_dni($dni)
    {
        $letra = substr($dni, -1);
        $numeros = substr($dni, 0, -1);
        if (substr('TRWAGMYFPDXBNJZSQVHLCKE', $numeros % 23, 1) == strtoupper($letra) && 1 == strlen($letra) && 8 == strlen($numeros)) {
            return true;
        } else {
            return false;
        }
    }

    public static function valida_nif_cif_nie($cif)
    {
        //Copyright ©2005-2011 David Vidal Serra. Bajo licencia GNU GPL.
        //Este software viene SIN NINGUN TIPO DE GARANTIA; para saber mas detalles
        //puede consultar la licencia en http://www.gnu.org/licenses/gpl.txt(1)
        //Esto es software libre, y puede ser usado y redistribuirdo de acuerdo
        //con la condicion de que el autor jamas sera responsable de su uso.
        //Returns: 1 = NIF ok, 2 = CIF ok, 3 = NIE ok, -1 = NIF bad, -2 = CIF bad, -3 = NIE bad, 0 = ??? bad

        $cif = strtoupper($cif);
        for ($i = 0; $i < 9; ++$i) {
            $num[$i] = substr($cif, $i, 1);
        }
        //si no tiene un formato valido devuelve error
        if (!preg_match('/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $cif)) {
            return 0;
        }
        //comprobacion de NIFs estandar
        if (preg_match('/(^[0-9]{8}[A-Z]{1}$)/', $cif)) {
            if ($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($cif, 0, 8) % 23, 1)) {
                return 1;
            } else {
                return -1;
            }
        }

        //comprobacion de NIEs
        if (preg_match('/^[XYZ]{1}/', $cif)) {
            if ($num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr(str_replace(array('X', 'Y', 'Z'), array('0', '1', '2'), $cif), 0, 8) % 23, 1)) {
                return 3;
            } else {
                return -3;
            }
        }

        //algoritmo para comprobacion de codigos tipo CIF
        //Sumamos los pares
        $sumaPares = $num[2] + $num[4] + $num[6];
        $sumaImpares = 0;
        for ($i = 1; $i < 8; $i += 2) {
            $sumaImpares += (2 * $num[$i] % 10) + intdiv(2 * $num[$i], 10);
        }
        $suma = $sumaPares + $sumaImpares;

        $sumaStr = (string) $suma;
        $n = 10 - intval(substr($sumaStr, -1));

        //comprobacion de NIFs especiales (se calculan como CIFs o como NIFs)
        if (preg_match('/^[KLM]{1}/', $cif)) {
            if ($num[8] == chr(64 + $n) || $num[8] == substr('TRWAGMYFPDXBNJZSQVHLCKE', substr($cif, 1, 8) % 23, 1)) {
                return 1;
            } else {
                return -1;
            }
        }
        //comprobacion de CIFs
        if (preg_match('/^[ABCDEFGHJNPQRSUVW]{1}/', $cif)) {
            if ($num[8] == chr(64 + $n) || $num[8] == substr($n, strlen($n) - 1, 1)) {
                return 2;
            } else {
                return -2;
            }
        }

        //si todavia no se ha verificado devuelve error
        return 0;
    }

    public static function getNumDNI($numDocumento)
    {
        if (null !== self::getDNILetra($numDocumento)) {
            $zenbakia = substr($numDocumento, 0, -1);

            return $zenbakia;
        } else {
            return $numDocumento;
        }
    }

    public static function getDNILetra($numDocumento)
    {
        $letra = substr($numDocumento, -1);
        if (!is_numeric($letra)) {
            return $letra;
        }

        return null;
    }

    public static function validateDate($date, $format = 'd/m/Y')
    {
        $d = \DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.

        return $d && $d->format($format) === $date;
    }
}
