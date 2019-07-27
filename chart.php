<?php

require_once 'autoloader.php';

$width = \Helper\Arr::get($_GET, 'width', 600);
$height = \Helper\Arr::get($_GET, 'height', 300);

$aData = \Helper\Arr::get($_POST, 'data', []);
if (count($aData) > 0) {
    try {
        $oMain = new \Chart\ThreeLinesBreak\Sequence($aData);
        $aBlocks = $oMain->getBlocks();
        $oDisplay = new \Chart\ThreeLinesBreak\Display($width, $height);
        $oDisplay->setBlocks($aBlocks);
        end($aData);
        $lastDate = key($aData);
        $lastPrice = $aData[$lastDate];
        echo $oDisplay->getOutput($lastPrice);
    } catch (\Chart\ChartException $ex) {
        echo "Ошибка: " . $ex->getMessage();
    }
}

