<?php

class Data
{
    const IDX_SHORT   = 0;
    const IDX_FULL    = 1;
    const IDX_CODE    = 2;
    const IDX_SUBTYPE = 3;
    const IDX_LOT     = 4;
    const IDX_ISIN    = 5;

    protected static $aData = [];

    public static function searchByText($needle, $columnNo=self::IDX_CODE)
    {
        self::getData();
        $record = [];
        foreach (self::$aData as $data) {
            if ($data[$columnNo] == $needle) {
                $record = $data;
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