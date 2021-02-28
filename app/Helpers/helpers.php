<?php

use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('dump')) {
    /**
     * @author Nicolas Grekas <p@tchwork.com>
     */
    function dump($var, ...$moreVars)
    {
        VarDumper::dump($var);

        foreach ($moreVars as $v) {
            VarDumper::dump($v);
        }

        if (1 < func_num_args()) {
            return func_get_args();
        }

        return $var;
    }
}

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $v) {
            VarDumper::dump($v);
        }

        exit(1);
    }
}

if (!function_exists('formatBytes')) {
    /**
     * Display a filesize in a human readable format
     * @param int $a_bytes
     * @return string
     */
    function formatBytes(int $a_bytes)
    {
        if(is_null($a_bytes)) {
            return '0 B';
        }
        if ($a_bytes < 1024) {
            return $a_bytes . ' B';
        } elseif ($a_bytes < 1048576) {
            return round($a_bytes / 1024, 2) . ' KB';
        } elseif ($a_bytes < 1073741824) {
            return round($a_bytes / 1048576, 2) . ' MB';
        } elseif ($a_bytes < 1099511627776) {
            return round($a_bytes / 1073741824, 2) . ' GB';
        } elseif ($a_bytes < 1125899906842624) {
            return round($a_bytes / 1099511627776, 2) . ' TB';
        } elseif ($a_bytes < 1152921504606846976) {
            return round($a_bytes / 1125899906842624, 2) . ' PB';
        } elseif ($a_bytes < 1180591620717411303424) {
            return round($a_bytes / 1152921504606846976, 2) . ' EB';
        } elseif ($a_bytes < 1208925819614629174706176) {
            return round($a_bytes / 1180591620717411303424, 2) . ' ZB';
        } else {
            return round($a_bytes / 1208925819614629174706176, 2) . ' YB';
        }
    }
}



if (!function_exists('removeAccents')) {

    /**
     * only remove accents from the passed string.
     * @param type $string
     * @param type $tolower
     * @return type
     */
    function removeAccents($string, $tolower = true)
    {
        $unwanted_array = array(
            'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y', 'Ğ' => 'G', 'İ' => 'I', 'Ş' => 'S', 'ğ' => 'g', 'ı' => 'i', 'ş' => 's', 'ü' => 'u',
            'ă' => 'a', 'Ă' => 'A', 'ș' => 's', 'Ș' => 'S', 'ț' => 't', 'Ț' => 'T', 'ć' => 'c'
        );
        $newstring = strtr($string, $unwanted_array);


        if ($tolower) {
            $newstring = strtolower($newstring);
        }

        return $newstring;
    }
}


if (!function_exists('removeSpecialChars')) {
    /**
     * Remove all special chars and replace them with Standard characters
     * source : https://stackoverflow.com/questions/3371697/replacing-accented-characters-php
     * @param type $string
     * @return string
     */
    function removeSpecialChars($string, $tolower = true)
    {
        $newstring = removeAccents($string, $tolower);
        $newstring = rtrim(preg_replace('~[^0-9a-z]+~i', '-', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($newstring, ENT_NOQUOTES, 'UTF-8'))), ' ');

        $newstring = preg_replace('/[^A-Za-z0-9\-\ ]/', '', $newstring);
        $newstring = preg_replace('/(^-*)|(-*$)/', '', $newstring);
        //Repalce multiple space by a single one
        $newstring = preg_replace('/\s+/', ' ', $newstring);
        $newstring = trim($newstring);

        return $newstring;
    }
}


if (!function_exists('reniceCertificate')) {

    /**
     * Renice a certificate for proper html display
     * @param type $cert
     * @return string
     */
    function reniceCertificate($cert)
    {
        $cert_array = explode("\n", $cert);
        $returnvalue = "";
        for ($i = 0; $i < count($cert_array); $i++) {
            $returnvalue .= $cert_array[$i] . "\n";
        }

        return trim($returnvalue);
    }
}

if (!function_exists('reorderCN')) {
    /**
     * Change the order (equivalent to "tac" under linux)
     * of the certificate CN and replace all separators from / to ,
     * @param type $cn
     * @return string
     */
    function reorderCN($cn)
    {
        $cn_array = explode("/", $cn);
        $new_cn = "";
        for ($i = count($cn_array) - 1; $i > 1; $i--) {
            $new_cn .= $cn_array[$i] . ",";
        }
        $new_cn .= $cn_array[1];
        return $new_cn;
    }
}
