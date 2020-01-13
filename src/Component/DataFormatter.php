<?php

namespace App\Component;

class DataFormatter
{
    /**
     * @param array $data
     * @param string $fields
     * @return string
     */
    public function format(array $data, $fields)
    {
        if (!empty($fields)) {
            $ignoreFields = array_diff(array_keys($data), explode(',', $fields));
            foreach ($ignoreFields as $ignoredField) {
                unset($data[$ignoredField]);
            }
        }
        if (count($data) > 1) {
            $result = json_encode($data);
        } else {
            $result = isset($data[$fields]) ? $data[$fields] : '';
        }
        return $result;
    }
}