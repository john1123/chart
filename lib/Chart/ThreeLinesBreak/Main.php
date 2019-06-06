<?php

namespace Chart\ThreeLinesBreak;

use Chart\ChartException;

class Main extends \Chart\Base
{
    const DEPTH = 3;
    protected $aData = [];

    public function setData(array $data)
    {
        $this->aData = $data;
    }

    public function getBlocks()
    {
        return $this->generateBlocks();
    }

    /**
     * @throws ChartException
     */
    protected function generateBlocks()
    {
        if (!is_array($this->aData) || count($this->aData) < 1) {
            throw new ChartException('Empty data');
        }
        //
        $aBlocks = [];
        $cnt = 0;
        $lastPrice = -1;
        $lastDate = '';
        foreach ($this->aData as $date => $aPrices) {
            $price = $aPrices['close'];
            if ($lastPrice < 0) {
                $lastPrice = $price;
                $lastDate = $date;
                continue;
            }
            if ($price > $lastPrice) {
                $aBlocks[] = [
                    'type' => '?',
                    'max' => $price,
                    'min' => $lastPrice,
                    'begin' => $lastDate,
                    'end' => $date,
                    'length' => $cnt++,
                ];
                $lastDate = $date;
                $lastPrice = $price;
                $cnt = 0;
            }
            if ($price < $lastPrice) {
                $aBlocks[] = [
                    'type' => '?',
                    'max' => $lastPrice,
                    'min' => $price,
                    'begin' => $date,
                    'end' => $lastDate,
                    'length' => $cnt++,
                ];
                $lastDate = $date;
                $lastPrice = $price;
                $cnt = 0;
            }
        }
        return $aBlocks;
    }
}