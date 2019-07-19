<?php

namespace Chart\ThreeLinesBreak;

use Chart\ChartException;

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
        foreach ($this->aBlocks as $key => $block) {
            $out .= $this->getBlock($block, $blockWidth, ($key == 0));
        }
        $out .= '</div>';
        $out .= '<div style="height:' . $this->divHeight . 'px;"></div>' . "\n";
        return $out;
    }

    protected $cntr = 0;

    protected function getBlock($block, $blockWidth, $isFirstBlock=false)
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

        // Блок высотой меньше 1 пикселя отображаем выстотой в 1 пиксель
        $blockHeight = $blockHeight < 1 ? 1 : $blockHeight;
        $style  = 'width:' . $blockWidth . 'px;height:' . $blockHeight . 'px;';
        $style .= 'left:' . $left . 'px;top:' . $top . 'px;';

        $pricesOut = '';
        if ($isFirstBlock || $class == 'green') {
            $pricesOut .= '<div style="position:relative;top:-12px">' . $block->getMaxPrice() . '</div>';

        }
        if ($isFirstBlock || $class == 'red') {
            $top = $isFirstBlock ? $blockHeight-17 : $blockHeight-3;
            $pricesOut .= '<div style="position:relative;top:' . $top . 'px">' . $block->getMinPrice() . '</div>';
        }
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
        throw new ChartException('Empty blocks list');
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
        throw new ChartException('Empty blocks list');
    }

    function translateCoordinate($price)
    {
        /** @var Block $block */
        $seqMin = $this->getMin();
        $seqMax = $this->getMax();
        $diff = $seqMax - $seqMin;
        $delta = $diff / $this->divHeight;

        return $delta == 0 ? 0 : ($price - $seqMin) / $delta;
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
