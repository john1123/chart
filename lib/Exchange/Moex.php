<?php

namespace Exchange;

use Cache\File as Cache;
use Chart\ChartException;
use Helper\Arr;

class Moex extends \Base
{

    protected $ticker;
    protected $oCache;
    protected $iPortopn = 99;
    const DATES_FORMAT = 'Y-m-d';
    const START_VALUES_DATE = '2001-01-01';
    const IDX_DATE = 16;
    const IDX_PRICE = 7;

    public function __construct($ticker, array $aOptions=[])
    {
        $this->ticker = $ticker;
        $this->oCache = new Cache($aOptions);
        parent::__construct($aOptions);
    }

    public function load($depth) {
        $startValTime = strtotime(self::START_VALUES_DATE);
        $arResult = [];
        $offset = 0;
        do {
            $timeTill = strtotime('-' . $offset . ' days');
            $timeFrom = strtotime('-' . ($offset+$this->iPortopn) . ' days');

            $dateTill = date(self::DATES_FORMAT, $timeTill);
            $dateFrom = date(self::DATES_FORMAT, $timeFrom);

            // Если одна из дат раньше минимально возможной - поменять её на минимально возможную
            $dateTill = $startValTime > $timeTill ? date(self::DATES_FORMAT, $startValTime) : $dateTill;
            $dateFrom = $startValTime > $timeFrom ? date(self::DATES_FORMAT, $startValTime) : $dateFrom;

            // Если даты начала и конца совпадают - выходим
            if ($dateFrom == $dateTill) {
                break;
            }
            $dataJSON = $this->loadInterval($dateFrom, $dateTill);
            $arDecoded = json_decode($dataJSON, true);
            $arColumns = Arr::get($arDecoded, 'history->columns', []);

            $idxDate = Arr::find($arColumns, "TRADEDATE");
            if ($idxDate < 0) {
                throw new ChartException("Ошибка. В результатах полученных от биржи поле TRADEDATE не найдено!");
            }
            $idxPrice = Arr::find($arColumns, "CLOSE");
            if ($idxPrice < 0) {
                throw new ChartException("Ошибка. В результатах полученных от биржи поле CLOSE не найдено!");
            }

            $arData = Arr::get($arDecoded, 'history->data', []);
            $this->shortName = Arr::get($arData, '0->2', '');
            foreach($arData as $data) {
                $arResult[ Arr::get($data, $idxDate, '') ] = Arr::get($data, $idxPrice, '');
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