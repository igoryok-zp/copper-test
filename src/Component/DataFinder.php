<?php

namespace App\Component;

class DataFinder
{
    /**
     * @param string $field
     * @param string $value
     * @param mixed $dataProvider
     * @return mixed
     */
    public function find($field, $value, $dataProvider)
    {
        $result = null;
        if (is_callable($dataProvider)) {
            do {
                $items = call_user_func($dataProvider);
                foreach ($items as $item) {
                    if (isset($item[$field]) && $item[$field] == $value) {
                        $result = $item;
                        break;
                    }
                }
            } while ($result == null && !empty($items));
        }
        return $result;
    }
}