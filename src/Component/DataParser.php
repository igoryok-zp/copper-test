<?php

namespace App\Component;

class DataParser
{
    /**
     * @param string $string
     * @return array
     */
    public function parse($string)
    {
        $result = [];
        $values = explode(';', $string);
        foreach ($values as $value) {
            @list($k, $v) = explode('=', $value);
            if (!empty($k)) {
                $result[trim($k)] = trim($v);
            }
        }
        return $result;
    }
}