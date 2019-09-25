<?php

namespace Helper;

class Arr
{
    const ARRAY_DELIMITER = '->';


    /**
     * Возвращает значение с указанным ключом из массива и удаляет это значение
     */
    public static function cut($array, $key, $default = null)
    {
        $value = self::get($array, $key, $default);
        //if ($value !== $default) {
            self::delete($array, $key);
        //}
        return $value;
    }

    /**
     * Удаляет значение с указанным ключом из массива
     */
    public static function delete($array, $keys)
    {
        $keys_arr = explode(self::ARRAY_DELIMITER, $keys);
        $value = $array;
        foreach ($keys_arr as $key) {
            $value = self::delete($value, $key);
        }
        return $value;
    }

    public static function get($array, $key, $default = null)
    {
        if (!is_array($array)) {
            return $default;
        }
        if (strpos($key, self::ARRAY_DELIMITER) !== false) {
            return self::get_multidimensional($array, $key, $default);
        }
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * Get data by case-insensitive key
     * @param $array
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function geti($array, $key, $default = null)
    {
        if (strpos($key, self::ARRAY_DELIMITER) !== false) {
            return self::geti_multidimensional($array, $key, $default);
        }
        foreach ($array as $k => $v) {
            if (strtolower($k) == strtolower($key)) {
                return $v;
            }
        }
        return $default;
    }

    public static function find($array, $value, $default = -1)
    {
        foreach ($array as $k => $v) {
            if ($array[$k] == $value) {
                return $k;
            }
        }
        return $default;
    }

    /**
     * Get data from multi-dimension array
     * @param $array - Source array
     * @param $keys - Multi-dimension key. String like: "parent=>child1=>child2"
     * @param $default - Default value
     * @return mixed
     */
    protected static function get_multidimensional($array, $keys, $default = null)
    {
        $keys_arr = explode(self::ARRAY_DELIMITER, $keys);
        $value = $array;
        foreach ($keys_arr as $key) {
            $value = self::get($value, $key, $default);
        }
        return $value;
    }

    /**
     * Get data from multi-dimension array by case-insensitive key
     * @param $array - Source array
     * @param $keys - Multi-dimension key. String like: "parent=>child1=>child2"
     * @param $default - Default value
     * @return mixed
     */
    protected static function geti_multidimensional($array, $keys, $default = null)
    {
        $keys_arr = explode(self::ARRAY_DELIMITER, $keys);
        $value = $array;
        foreach ($keys_arr as $key) {
            $value = self::geti($value, $key, $default);
        }
        return $value;
    }
}