<?php

class Moex
{

    protected $objCache;
    const DATES_FORMAT = 'Y-m-d';

    public function __construct()
    {
        $this->objCache = new Cache\File();
    }

    public function load($ticker, $depth=30) {
        $arResult = [];
        $this->ticker = $ticker;
        $offset = 0;
        do {
            $dateTill = date(self::DATES_FORMAT, strtotime('-' . $offset . ' days'));
            $dateFrom = date(self::DATES_FORMAT, strtotime('-' . ($offset+99) . ' days'));

            $dataJSON = $this->loadInterval($ticker, $dateFrom, $dateTill);
            $arDecoded = json_decode($dataJSON, true);
            $arData = $arDecoded['history']['data'];
            $this->shortName = $arData[0][2];
            foreach($arData as $data) {
                $arResult[ $data[1] ] = $data[11];
            }
            $offset += 100;
            if (count($arResult) >= $depth) {
                break;
            }
        } while (true);
        krsort($arResult);
        array_splice($arResult, $depth);
        ksort($arResult);
        $this->data = $arResult;
        return $arResult;
    }

    public function loadInterval($ticker, $dateFrom, $dateTill='') {
        if (strlen($dateTill) == 0) {
            $dateTill = date(self::DATES_FORMAT);
        }
        $cacheName = strtolower($ticker . '.from-' . $dateFrom . '.till-' . $dateTill);
        $dataJSON = $this->objCache->get($cacheName);
        if(strlen($dataJSON) > 0) {
            return $dataJSON;
        }
        $uri  = 'https://iss.moex.com/iss/history/engines/stock/markets/shares/boards/TQBR/securities/'.$ticker.'.json';
        $uri .= '?from=' . $dateFrom;
        $uri .= '&till=' . $dateTill;
        $uri .= '&iss.meta=off';
        $dataJSON = file_get_contents($uri);
        $this->objCache->set($cacheName, $dataJSON);
        return $dataJSON;
    }

}