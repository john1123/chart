<?php

namespace Exchange;

use Cache\File as Cache;
use Helper\Arr;

class Moex extends \Base
{

    protected $ticker;
    protected $oCache;
    const DATES_FORMAT = 'Y-m-d';

    public function __construct($ticker, array $aOptions=[])
    {
        $this->ticker = $ticker;
        $this->oCache = new Cache($aOptions);
        parent::__construct($aOptions);
    }

    public function load($depth) {
        $arResult = [];
        $offset = 0;
        do {
            $dateTill = date(self::DATES_FORMAT, strtotime('-' . $offset . ' days'));
            $dateFrom = date(self::DATES_FORMAT, strtotime('-' . ($offset+99) . ' days'));

            $dataJSON = $this->loadInterval($dateFrom, $dateTill);
            $arDecoded = json_decode($dataJSON, true);
            $arData = Arr::get($arDecoded, 'history->data', []);
            $this->shortName = Arr::get($arData, '0->2', '');
            foreach($arData as $data) {
                $arResult[ Arr::get($data, 1, '') ] = Arr::get($data, 11, '');
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

    public function loadInterval($dateFrom, $dateTill='') {
        if (strlen($dateTill) == 0) {
            $dateTill = date(self::DATES_FORMAT);
        }
        $cacheName = strtolower($this->ticker . '.from-' . $dateFrom . '.till-' . $dateTill);
        $dataJSON = $this->oCache->get($cacheName);
        if(strlen($dataJSON) > 0) {
            return $dataJSON;
        }
        $uri  = 'https://iss.moex.com/iss/history/engines/stock/markets/shares/boards/TQBR/securities/'.$this->ticker.'.json';
        $uri .= '?from=' . $dateFrom;
        $uri .= '&till=' . $dateTill;
        $uri .= '&iss.meta=off';
        $dataJSON = file_get_contents($uri);
        $this->oCache->set($cacheName, $dataJSON);
        return $dataJSON;
    }

}