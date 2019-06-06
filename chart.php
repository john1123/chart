<?php

require_once 'autoloader.php';

$code = \Helper\Arr::get($_GET, 'code', '');
$depth = \Helper\Arr::get($_GET, 'depth', 60);
$width = \Helper\Arr::get($_GET, 'width', 600);
$height = \Helper\Arr::get($_GET, 'height', 300);

if (strlen($code) > 0) {
    $oMoex = new \Exchange\Moex($code);
    $aData = $oMoex->load($depth);

    $oMain = new \Chart\ThreeLinesBreak\Sequence($aData);
    $aBlocks = $oMain->getBlocks();
    $oDisplay = new \Chart\ThreeLinesBreak\Display($width, $height);
    $oDisplay->setBlocks($aBlocks);
    echo $oDisplay->getOutput();
}

