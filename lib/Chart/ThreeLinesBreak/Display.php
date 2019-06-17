<?php

namespace Chart\ThreeLinesBreak;

class Display extends \Chart\Base
{
    protected $divWidth;
    protected $divHeight;

    /** @var Block[] */
    protected $aBlocks = [];

    public function __construct($width, $height)
    {
        $this->divWidth = $width;
        $this->divHeight = $height;
    }

    public function setBlocks(array $blocks)
    {
        $this->aBlocks = $blocks;
    }

    public function getOutput()
    {
        $blockWidth = round($this->divWidth / count($this->aBlocks));
        $out =  '<div class="chart" style="width:' . $this->divWidth . 'px;height:' . $this->divHeight . 'px">';
        foreach ($this->aBlocks as $block) {
            $withPrices = count($this->aBlocks) > 30 ? false : true;
            $out .= $this->getBlock($block, $blockWidth, $withPrices);
        }
        $out .= '</div>';
        $out .= '<div style="height:' . $this->divHeight . 'px;"></div>' . "\n";
        return $out;
    }

    protected $cntr = 0;

    protected function getBlock($block, $blockWidth, $withPrices=true)
    {
        /** @var Block $block */

        $coordMin = round($this->translateCoordinate($block->getMinPrice()));
        $coordMax = round($this->translateCoordinate($block->getMaxPrice()));

        $blockHeight = $coordMax - $coordMin;
        $left = $blockWidth * $this->cntr;
        $this->cntr++;
        $top = $this->divHeight - $coordMax;

        $class = $block->getOpenPrice() < $block->getClosePrice() ? 'green' : 'red';

        if ($block->getOpenPrice() > $block->getClosePrice()) {
            $title = $block->getOpenPrice();
            $title .= '&#13;&#10;';
            $title .= $block->getClosePrice();
        } else {
            $title = $block->getClosePrice();
            $title .= '&#13;&#10;';
            $title .= $block->getOpenPrice();
        }
//        if ($block->getOpenPrice() > $block->getClosePrice()) {
//            $title = $block->getOpenDate() . ': ' . $block->getOpenPrice();
//            //$title .= '&#13;&#10;' . self::niceDifference($block->getOpenDate(), $block->getCloseDate()) . '&#13;&#10;';
//            $title .= '&#13;&#10;';
//            $title .= $block->getCloseDate() . ': ' . $block->getClosePrice();
//        } else {
//            $title = $block->getCloseDate() . ': ' . $block->getClosePrice();
//            //$title .= '&#13;&#10;' . self::niceDifference($block->getOpenDate(), $block->getCloseDate()) . '&#13;&#10;';
//            $title .= '&#13;&#10;';
//            $title .= $block->getOpenDate() . ': ' . $block->getOpenPrice();
//        }

        $blockHeight = !$withPrices && $blockHeight < 1 ? 1 : $blockHeight;
        $style  = 'width:' . $blockWidth . 'px;height:' . $blockHeight . 'px;';
        $style .= 'left:' . $left . 'px;top:' . $top . 'px;';

        $pricesOut = $withPrices ? '<div style="top:-10px">' . $block->getMaxPrice() . '</div><div style="top:' . ($blockHeight-13) . 'px">' . $block->getMinPrice() . '</div>' : '';
        $out = '<div class="bar ' . $class . '" title="' . $title . '" style="' . $style . '">' . $pricesOut . '</div>';
        return $out;
    }

    protected function getMin()
    {
        if (count($this->aBlocks) > 0) {
            $min = $this->aBlocks[0]->getMinPrice();
            for ($i=1; $i<count($this->aBlocks); $i++) {
                $min = min($this->aBlocks[$i]->getMinPrice(), $min);
            }
            return $min;
        }
        throw new Exception('Empty blocks list');
    }
    protected function getMax()
    {
        if (count($this->aBlocks) > 0) {
            $max = $this->aBlocks[0]->getMaxPrice();
            for ($i=1; $i<count($this->aBlocks); $i++) {
                $max = max($this->aBlocks[$i]->getMaxPrice(), $max);
            }
            return $max;
        }
        throw new Exception('Empty blocks list');
    }

    function translateCoordinate($price)
    {
        /** @var Block $block */
        $seqMin = $this->getMin();
        $seqMax = $this->getMax();
        $diff = $seqMax - $seqMin;
        $delta = $diff / $this->divHeight;

        return ($price - $seqMin) / $delta;
    }

    static function niceDifference($date1, $date2, $format = 'd.m.Y')
    {
        $diff = (strtotime($date1) - strtotime($date2))/3600/24;
        $diff = abs($diff);
        $lastDigit = $diff % 10;
        $suffix = ' дней';
        if ($lastDigit == 1) {
            $suffix = ' день';
        } elseif ($lastDigit == 2 || $lastDigit == 3 || $lastDigit == 4) {
            $suffix = ' дня';
        }
        return $diff . $suffix;
    }
}
