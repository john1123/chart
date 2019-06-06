<?php

class Data
{
    const IDX_TECH    = 0;
    const IDX_FULL    = 1;
    const IDX_SHORT   = 2;
    const IDX_CODE    = 3;
    const IDX_SUBTYPE = 4;
    const IDX_LOT     = 5;
    const IDX_ISIN    = 6;

    protected static $aData = [];

    public static function searchData($code, $column=self::IDX_CODE)
    {
        self::getData();
        $record = [];
        foreach (self::$aData as $record) {
            if ($record[$column] == $code) {
                break;
            }
        }
        return $record;
    }
    public static function getData()
    {
        if (count(self::$aData) < 1) {
            $string = file_get_contents('data.json');
            self::$aData = json_decode($string, true);
        }
        return self::$aData;
    }

}