<?php

namespace App\Utilities;

use DateTime;

class Funciones
{

    public static function bc()
    {
        //usa escala de precision 2
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

        $argv = func_get_args();
        $string = str_replace(' ', '', "({$argv[0]})");
        $scale = 2;

        $operations = array();
        if (strpos($string, '^') !== false)
            $operations[] = '\^';
        if (strpbrk($string, '*/%') !== false)
            $operations[] = '[\*\/\%]';
        if (strpbrk($string, '+-') !== false)
            $operations[] = '[\+\-]';
        if (strpbrk($string, '<>!=') !== false)
            $operations[] = '<|>|=|<=|==|>=|!=|<>';

        $string = preg_replace_callback('/\$([0-9\.]+)/', function ($matches) {
            return $argv[$matches[1]];
        }, $string);

        while (preg_match('/\(([^\)\(]*)\)/', $string, $match)) {
            foreach ($operations as $operation) {
                if (preg_match("/([+-]{0,1}[0-9\.]+)($operation)([+-]{0,1}[0-9\.]+)/", $match[1], $m)) {
                    switch ($m[2]) {
                        case '+':
                            $result = bcadd($m[1], $m[3], $scale);
                            break;
                        case '-':
                            $result = bcsub($m[1], $m[3], $scale);
                            break;
                        case '*':
                            $result = bcmul($m[1], $m[3], $scale);
                            break;
                        case '/':
                            $result = bcdiv($m[1], $m[3], $scale);
                            break;
                        case '%':
                            $result = bcmod($m[1], $m[3], $scale);
                            break;
                        case '^':
                            $result = bcpow($m[1], $m[3], $scale);
                            break;
                        case '==':
                        case '=':
                            $result = bccomp($m[1], $m[3]) == 0;
                            break;
                        case '>':
                            $result = bccomp($m[1], $m[3]) == 1;
                            break;
                        case '<':
                            $result = bccomp($m[1], $m[3]) == -1;
                            break;
                        case '>=':
                            $result = bccomp($m[1], $m[3]) >= 0;
                            break;
                        case '<=':
                            $result = bccomp($m[1], $m[3]) <= 0;
                            break;
                        case '<>':
                        case '!=':
                            $result = bccomp($m[1], $m[3]) != 0;
                            break;
                    }
                    $match[1] = str_replace($m[0], $result, $match[1]);
                }
            }
            $string = str_replace($match[0], $match[1], $string);
        }

        return $string;
    }

    public static function bc62()
    {
        //usa escala de precision 6
        //redondea al final a escala de precision 2
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

        $argv = func_get_args();
        $string = str_replace(' ', '', "({$argv[0]})");
        $scale = 6;

        $operations = array();
        if (strpos($string, '^') !== false)
            $operations[] = '\^';
        if (strpbrk($string, '*/%') !== false)
            $operations[] = '[\*\/\%]';
        if (strpbrk($string, '+-') !== false)
            $operations[] = '[\+\-]';
        if (strpbrk($string, '<>!=') !== false)
            $operations[] = '<|>|=|<=|==|>=|!=|<>';

        //dump($string);
        //$string = preg_replace('/\$([0-9\.]+)/e', '$argv[$1]', $string);

        $string = preg_replace_callback('/\$([0-9\.]+)/', function ($matches) {
            //dump($argv);
            //dump($matches);
            //dump($matches[1]);
            //dump($argv[$matches[1]]);
            return $argv[$matches[1]];
        }, $string);

        //dump($string);

        while (preg_match('/\(([^\)\(]*)\)/', $string, $match)) {
            foreach ($operations as $operation) {
                if (preg_match("/([+-]{0,1}[0-9\.]+)($operation)([+-]{0,1}[0-9\.]+)/", $match[1], $m)) {
                    switch ($m[2]) {
                        case '+':
                            $result = bcadd($m[1], $m[3], $scale);
                            break;
                        case '-':
                            $result = bcsub($m[1], $m[3], $scale);
                            break;
                        case '*':
                            $result = bcmul($m[1], $m[3], $scale);
                            break;
                        case '/':
                            $result = bcdiv($m[1], $m[3], $scale);
                            break;
                        case '%':
                            $result = bcmod($m[1], $m[3], $scale);
                            break;
                        case '^':
                            $result = bcpow($m[1], $m[3], $scale);
                            break;
                        case '==':
                        case '=':
                            $result = bccomp($m[1], $m[3]) == 0;
                            break;
                        case '>':
                            $result = bccomp($m[1], $m[3]) == 1;
                            break;
                        case '<':
                            $result = bccomp($m[1], $m[3]) == -1;
                            break;
                        case '>=':
                            $result = bccomp($m[1], $m[3]) >= 0;
                            break;
                        case '<=':
                            $result = bccomp($m[1], $m[3]) <= 0;
                            break;
                        case '<>':
                        case '!=':
                            $result = bccomp($m[1], $m[3]) != 0;
                            break;
                    }
                    $match[1] = str_replace($m[0], $result, $match[1]);
                }
            }
            $string = str_replace($match[0], $match[1], $string);
        }

        if (is_numeric($string)) {
            $string = round($string, 2, PHP_ROUND_HALF_UP);
            $string = number_format($string, 2, '.', '');
        }
        return $string;
    }

    public static function bc42()
    {
        //usa escala de precision 4
        //redondea al final a escala de precision 2
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

        $argv = func_get_args();
        $string = str_replace(' ', '', "({$argv[0]})");
        $scale = 4;

        $operations = array();
        if (strpos($string, '^') !== false)
            $operations[] = '\^';
        if (strpbrk($string, '*/%') !== false)
            $operations[] = '[\*\/\%]';
        if (strpbrk($string, '+-') !== false)
            $operations[] = '[\+\-]';
        if (strpbrk($string, '<>!=') !== false)
            $operations[] = '<|>|=|<=|==|>=|!=|<>';

        //dump($string);
        //$string = preg_replace('/\$([0-9\.]+)/e', '$argv[$1]', $string);

        $string = preg_replace_callback('/\$([0-9\.]+)/', function ($matches) {
            //dump($argv);
            //dump($matches);
            //dump($matches[1]);
            //dump($argv[$matches[1]]);
            return $argv[$matches[1]];
        }, $string);

        //dump($string);

        while (preg_match('/\(([^\)\(]*)\)/', $string, $match)) {
            foreach ($operations as $operation) {
                if (preg_match("/([+-]{0,1}[0-9\.]+)($operation)([+-]{0,1}[0-9\.]+)/", $match[1], $m)) {
                    switch ($m[2]) {
                        case '+':
                            $result = bcadd($m[1], $m[3], $scale);
                            break;
                        case '-':
                            $result = bcsub($m[1], $m[3], $scale);
                            break;
                        case '*':
                            $result = bcmul($m[1], $m[3], $scale);
                            break;
                        case '/':
                            $result = bcdiv($m[1], $m[3], $scale);
                            break;
                        case '%':
                            $result = bcmod($m[1], $m[3], $scale);
                            break;
                        case '^':
                            $result = bcpow($m[1], $m[3], $scale);
                            break;
                        case '==':
                        case '=':
                            $result = bccomp($m[1], $m[3]) == 0;
                            break;
                        case '>':
                            $result = bccomp($m[1], $m[3]) == 1;
                            break;
                        case '<':
                            $result = bccomp($m[1], $m[3]) == -1;
                            break;
                        case '>=':
                            $result = bccomp($m[1], $m[3]) >= 0;
                            break;
                        case '<=':
                            $result = bccomp($m[1], $m[3]) <= 0;
                            break;
                        case '<>':
                        case '!=':
                            $result = bccomp($m[1], $m[3]) != 0;
                            break;
                    }
                    $match[1] = str_replace($m[0], $result, $match[1]);
                }
            }
            $string = str_replace($match[0], $match[1], $string);
        }

        if (is_numeric($string)) {
            $string = round($string, 2, PHP_ROUND_HALF_UP);
            $string = number_format($string, 2, '.', '');
        }
        return $string;
    }

    public static function bc4()
    {
        //usa escala de precision 4
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

        $argv = func_get_args();
        $string = str_replace(' ', '', "({$argv[0]})");
        $scale = 4;

        $operations = array();
        if (strpos($string, '^') !== false)
            $operations[] = '\^';
        if (strpbrk($string, '*/%') !== false)
            $operations[] = '[\*\/\%]';
        if (strpbrk($string, '+-') !== false)
            $operations[] = '[\+\-]';
        if (strpbrk($string, '<>!=') !== false)
            $operations[] = '<|>|=|<=|==|>=|!=|<>';

        //dump($string);
        //$string = preg_replace('/\$([0-9\.]+)/e', '$argv[$1]', $string);

        $string = preg_replace_callback('/\$([0-9\.]+)/', function ($matches) {
            //dump($argv);
            //dump($matches);
            //dump($matches[1]);
            //dump($argv[$matches[1]]);
            return $argv[$matches[1]];
        }, $string);

        //dump($string);

        while (preg_match('/\(([^\)\(]*)\)/', $string, $match)) {
            foreach ($operations as $operation) {
                if (preg_match("/([+-]{0,1}[0-9\.]+)($operation)([+-]{0,1}[0-9\.]+)/", $match[1], $m)) {
                    switch ($m[2]) {
                        case '+':
                            $result = bcadd($m[1], $m[3], $scale);
                            break;
                        case '-':
                            $result = bcsub($m[1], $m[3], $scale);
                            break;
                        case '*':
                            $result = bcmul($m[1], $m[3], $scale);
                            break;
                        case '/':
                            $result = bcdiv($m[1], $m[3], $scale);
                            break;
                        case '%':
                            $result = bcmod($m[1], $m[3], $scale);
                            break;
                        case '^':
                            $result = bcpow($m[1], $m[3], $scale);
                            break;
                        case '==':
                        case '=':
                            $result = bccomp($m[1], $m[3]) == 0;
                            break;
                        case '>':
                            $result = bccomp($m[1], $m[3]) == 1;
                            break;
                        case '<':
                            $result = bccomp($m[1], $m[3]) == -1;
                            break;
                        case '>=':
                            $result = bccomp($m[1], $m[3]) >= 0;
                            break;
                        case '<=':
                            $result = bccomp($m[1], $m[3]) <= 0;
                            break;
                        case '<>':
                        case '!=':
                            $result = bccomp($m[1], $m[3]) != 0;
                            break;
                    }
                    $match[1] = str_replace($m[0], $result, $match[1]);
                }
            }
            $string = str_replace($match[0], $match[1], $string);
        }

        return $string;
    }

    public static function validateDatetime($date, $format = 'Y-m-d H:i:s')
    {
        //Y-m-d
        //d/m/Y
        //H:i
        $d = @DateTime::createFromFormat($format, $date);
        return (bool) ($d && $d->format($format) == $date);
    }
    public function dateIsBetween($from, $to, $date = "now")
    {
        $date = new \DateTime($date);
        $from = new \DateTime($from);
        $to = new \DateTime($to);
        if ($date >= $from && $date <= $to) {
            return true;
        }
        return false;
    }
    public  static function stringToDate($string, $inputFormat = 'Y-m-d', $outputFormat = "Y-m-d", $returnOriginalValueIfFail = false)
    {
        $date = $year = $month = $day = NULL;
        if ($string == NULL)
            return NULL;
        if ($inputFormat == 'c' && strlen($string) == 29) {
            //"2015-12-31T03:56:32.418-03:00"
            $temp = substr($string, 0, 10) . " " . substr($string, 11, 8);
            $string = $temp;
            $inputFormat = 'Y-m-d H:i:s';
            unset($temp);
        }
        if ($inputFormat == 'c2' && strlen($string) == 25) {
            //"1985-07-03T12:00:00-03:00"
            $temp = substr($string, 0, 10) . " " . substr($string, 11, 8);
            $string = $temp;
            $inputFormat = 'Y-m-d H:i:s';
            unset($temp);
        }
        if ($inputFormat == 'Y-m-d H:i:s.uuu' && strlen($string) == 23) {
            //"2016-05-02 12:28:27.010"
            $temp = substr($string, 0, 19);
            $string = $temp;
            $inputFormat = 'Y-m-d H:i:s';
            unset($temp);
        }
        //si cumple con el formato de entrada definido
        if (self::validateDatetime($string, $inputFormat) == true) {
            if ($inputFormat == 'Y-m-d')
                list($year, $month, $day) = explode('-', $string);
            if ($inputFormat == 'Y/m/d')
                list($year, $month, $day) = explode('/', $string);
            if ($inputFormat == 'Y.m.d')
                list($year, $month, $day) = explode('.', $string);
            if ($inputFormat == 'd-m-Y')
                list($day, $month, $year) = explode('-', $string);
            if ($inputFormat == 'd/m/Y')
                list($day, $month, $year) = explode('/', $string);
            if ($inputFormat == 'd.m.Y')
                list($day, $month, $year) = explode('.', $string);
            if ($inputFormat == 'm-d-Y')
                list($month, $day, $year) = explode('-', $string);
            if ($inputFormat == 'm/d/Y')
                list($month, $day, $year) = explode('/', $string);
            if ($inputFormat == 'm.d.Y')
                list($month, $day, $year) = explode('.', $string);
            if (!empty($month) && !empty($day) && !empty($year))
                $date = date($outputFormat, mktime(0, 0, 0, $month, $day, $year));
            if (empty($month) && empty($day) && empty($year) && empty($date)) {
                //$date=DateTime::createFromFormat($inputFormat, $string)->format($outputFormat);
                $d = @DateTime::createFromFormat($inputFormat, $string);
                if ($d) {
                    $date = $d->format($outputFormat);
                }
            }
        }
        if ($date === NULL && $returnOriginalValueIfFail == true) {
            $date = $string;
        }
        return $date;
    }

    public static function tieneCaracteresEspeciales($string)
    {
        //determino si un string tiene caracteres especiales NO permitidos
        //permitidos
        $arrSpecialChars = [
            'á', 'é', 'í', 'ó', 'ú', 'ñ',
            'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ',
            'ü', 'Ü',
            ",", " ", "'", "-", "_",
            "\.", "\/", "\\", "\(", "\)", "\+"
        ];
        $strSpecialChars = implode("", $arrSpecialChars);
        $preg = "#^[a-zA-Z0-9{$strSpecialChars}]+$#";
        if (!preg_match($preg, $string)) {
            //Si tiene al menos un caracter fuera de la lista
            return 1;
        }
        return 0;
    }

    public static function convertirStringNumericoANumero(&$array)
    {
        //fix valores array string a numero
        if (!empty($array) && is_array($array)) {
            array_walk_recursive(
                $array,
                function (&$value) {
                    //si es un numero con decimales
                    if ($value != null && is_numeric($value)) {
                        $value = $value + 0;
                    }
                }
            );
        }
    }
}
