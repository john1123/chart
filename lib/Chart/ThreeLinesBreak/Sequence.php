<?php

namespace Chart\ThreeLinesBreak;

use Chart\ChartException;

class Sequence extends \Chart\Base
{
    /** Сколько блоков учитывается */
    const DEPTH = 3;
    const MAX_VALUE = -9999;
    const MIN_VALUE = 9999;
    protected $aData = [];
    /** @var Block[] */
    protected $aBlocks = [];

    public function __construct(array $data)
    {
        $this->aData = $data;
        $this->generateBlocks();
    }

    public function getBlocks()
    {
        //if (count($this->aBlocks) == 0) $this->generateBlocks();
        return $this->aBlocks;
    }

    /**
     * @throws ChartException
     */
    protected function generateBlocks()
    {
        if (!is_array($this->aData) || count($this->aData) < 1) {
            throw new ChartException('Данных об биржи не получено');
        }
        $this->aBlocks = [];
        $idx = 0;
        foreach ($this->aData as $date => $price) {
            // Если блок в стэке есть
            if (array_key_exists($idx, $this->aBlocks)) {
                //Если блок в стэке ещё не был закрыт
                if ($this->aBlocks[$idx]->getClosePrice() < 0) {
                    $this->aBlocks[$idx]->closeBlock($price, $date);
                    //$idx++;
                    continue;
                }
                //
                $seqMin = $this->getSequenceMin();
                $seqMax = $this->getSequenceMax();
                if ($price >= $seqMin && $price <= $seqMax) { continue; }
                if ($price < $seqMin) {
                    $this->aBlocks[$idx+1] = new Block($this->aBlocks[$idx]->getMinPrice(), $this->aBlocks[$idx]->getMinDate());
                    $this->aBlocks[$idx+1]->closeBlock($price, $date);
                } else {
                    $this->aBlocks[$idx+1] = new Block($this->aBlocks[$idx]->getMaxPrice(), $this->aBlocks[$idx]->getMaxDate());
                    $this->aBlocks[$idx+1]->closeBlock($price, $date);
                }
            } else {
                $this->aBlocks[$idx] = new Block($price, $date);
                continue;
            }
            $idx++;
        }
    }

    protected function getSequenceMin()
    {
        $min = -1;
        /** @var Block $block */
        $block = end($this->aBlocks);
        $counter = self::DEPTH;
        //
        do {
            if (is_object($block)) {
                $min = is_float($min) ? min($min, $block->getMinPrice()) : $block->getMinPrice();
            }
            $counter--;
            if ($counter < 1) {
                break;
            }
        } while ($block = prev($this->aBlocks));
        if ($min < 0) {
            throw new ChartException('Не удалось найти инимальное значение', self::EXCEPTION_OUT_OF_RANGE);
        }
        return $min;
    }

    protected function getSequenceMax()
    {
        $max = -1;
        /** @var Block $block */
        $block = end($this->aBlocks);
        $counter = self::DEPTH;
        //
        do {
            if (is_object($block)) {
                $max = is_float($max) ? max($max, $block->getMaxPrice()) : $block->getMaxPrice();
            }
            $counter--;
            if ($counter < 1) {
                break;
            }
        } while ($block = prev($this->aBlocks));
        if ($max < 0) {
            throw new ChartException('Не удалось найти максимальное значение', self::EXCEPTION_OUT_OF_RANGE);
        }
        return $max;
    }
    public function getMin()
    {
        //if (count($this->aBlocks) == 0) $this->generateBlocks();
        $min = self::MIN_VALUE;
        foreach ($this->aBlocks as $block) {
            $min = min($min, $block->getMinPrice());
        }
        return $min;
    }

    public function getMax()
    {
        //if (count($this->aBlocks) == 0) $this->generateBlocks();
        $max = self::MAX_VALUE;
        foreach ($this->aBlocks as $block) {
            $max = max($max, $block->getMaxPrice());
        }
        return $max;
    }

    public function getStep()
    {
        return 100;
    }
}