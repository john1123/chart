<?php

namespace Chart\ThreeLinesBreak;

use Chart\ChartException;

class Block extends \Chart\Base
{
    protected $openPrice = -1;
    protected $openDate = '';
    protected $closePrice = -1;
    protected $closeDate = '';

    public function __construct($openPrice, $openDate='')
    {
        $this->openBlock($openPrice, $openDate);
    }

    public function openBlock($price, $date = '')
    {
        if ($this->openPrice < 0) {
            $this->openPrice = floatval($price);
            $this->openDate = $date;
        } else {
            throw new ChartException('block already opened');
        }
    }
    public function closeBlock($price, $date = '')
    {
        if ($this->closePrice < 0) {
            $this->closePrice = floatval($price);
            $this->closeDate = $date;
        } else {
            throw new ChartException('block already closed');
        }
    }

//    public function __toString()
//    {
//        $direction = $this->isRed() ? '↓' : '↑';
//        return $direction . ' from ' . $this->getMinPrice()
//            . ' to ' . $this->getMaxPrice();
//    }
    //public function __debugInfo() {return [$this->__toString()];}

    public function getMinPrice() { return min($this->openPrice, $this->closePrice); }
    public function getMinDate() { return $this->openPrice < $this->closePrice ? $this->getOpenDate() : $this->getCloseDate(); }
    public function getMaxPrice() { return max($this->openPrice, $this->closePrice); }
    public function getMaxDate() { return $this->openPrice > $this->closePrice ? $this->getCloseDate() : $this->getOpenDate(); }
    public function getOpenPrice() { return $this->openPrice; }
    public function getOpenDate() { return date('d.m.Y', strtotime($this->openDate)); }
    public function getClosePrice() { return $this->closePrice; }
    public function getCloseDate() { return date('d.m.Y', strtotime($this->closeDate)); }

}
