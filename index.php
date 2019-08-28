<?php

require_once 'autoloader.php';

$fullText='';
$messages = [];
$aData = [];

// Настройки
session_start();
$defaultIntDepth = 50;
$defaultBoolShowLastPrice = false;
$sSettings = \Helper\Arr::get($_POST, 'settings', '');
if (strlen($sSettings) > 0) {
    $aSettings = json_decode($sSettings, true);
    if (array_key_exists('showLastPrice', $aSettings)) {
        $_SESSION['showLastPrice'] = $aSettings['showLastPrice'];
    }
    if (array_key_exists('depth', $aSettings)) {
        $_SESSION['depth'] = intval($aSettings['depth']);
        // Принудительно уменьшаем глубину до 100 если она больше
        if ($_SESSION['depth'] > 100) {
            $_SESSION['depth'] = 100;
        }
    }
    header('Content-Type: application/json');
    echo '{"success":"true","message":"Настройки установлены"}';
    die;
}

$code = strtoupper(\Helper\Arr::get($_GET, 'code', ''));
if (strlen($code) > 0) {
    $isCodeValid = count(Data::searchByText($code)) > 0;
    if ($isCodeValid == false) {
        $messages[] = [
            'type'    => 'danger',
            'message' => 'Код бумаги не найден'
        ];
        $code = '';
    }
}

$strRaw = \Helper\Arr::get($_POST, 'data', '');
if (strlen($strRaw) > 0) {
    $isReverse = \Helper\Arr::get($_POST, 'reverse', 'false');
    $aData = [];
    $aRawData = explode(PHP_EOL, $strRaw);
    foreach ($aRawData as $sLine) {
        $aParts =  preg_split("/[\s]+/", $sLine);
        if (count($aParts) > 1) {
            $price = floatval($aParts[1]);
            if ($price > 0) {
                $aData[$aParts[0]] = $price;
            }
        } else {
            $price = floatval($aParts[0]);
            if ($price > 0) {
                $aData[] = $price;
            }
        }
    }
    if ($isReverse == 'true') {
        $aData = array_reverse($aData);
    }
    $depth = count($aData);
    if ($depth < 1) {
        $messages[] = [
            'type'    => 'danger',
            'message' => '<strong>Не могу построить график!</strong> Данные не распознаны.'
        ];
    } elseif ($depth < 2) {
        $messages[] = [
            'type'    => 'warning',
            'message' => '<strong>Не могу построить график!</strong> Нельзя простроить график всего с одной ценой.'
        ];
        $aData = [];
    }
} else {
    $depth = \Helper\Arr::get($_SESSION, 'depth', $defaultIntDepth);
    if (strlen($code) > 0) {
        $aActive = Data::searchByText($code);
        $fullText = '[' . $code . '] ' . $aActive[Data::IDX_FULL];

        $oMoex = new \Exchange\Moex($code, [
            'cacheDirectory' => __DIR__ . DIRECTORY_SEPARATOR . 'cache',
            'cacheRefresh' => \Helper\Arr::get($_GET, 'refresh', 'false'),
        ]);
        $aData = $oMoex->load($depth);

        // убираем все дни с нулевой ценой
        $emptyDays = 0;
        foreach ($aData as $date => $price) {
            if ($price > 0) {} else {
                $emptyDays++;
                unset($aData[$date]);
            }
        }
        if ($emptyDays > 0) {
            $messages[] = [
                'type'    => 'warning',
                'message' => '<strong>Внимание!</strong> В течение нескольких деней (' . $emptyDays . ') по инструменту не было сделок. Эти дни будут пропущены на графике.'
            ];
        }
    }
}

if (count($aData) > 0) {
    // Получаем последнюю дату и последнюю цену
    $aKeys = array_keys($aData);
    $lastDate = array_pop($aKeys);
    $lastPrice = $aData[$lastDate];
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $fullText ?></title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/select2.min.css">
    <link rel="stylesheet" href="css/select2-bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="<?= $_SERVER["SCRIPT_NAME"] ?>">
                <img alt="Brand" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAACXBIWXMAAAsSAAALEgHS3X78AAAE+UlEQVRIiaWW3WsUVxjGn/c9uzvZrDGJFrG2klTQIqQgpIWUurkwLUJBKYkfUBsb7/Kh10KtKaVXarE39ar0otAK5sN/oI2FxItECFoVrPEmS1qJ7u7MZndnNzNzznl7kQ+jSVtqn+Fw5ur3nue8Z+Y5hBeklFLGGAtAWltb9x4/duxEe7r9/ebm5j2O49QDQBAEC7OzszPjE+O/XBsa+ml6evoBgYgVszHGvMhcC48BQEtLy66R4ZGrC14h0GEklbIvBdcTL++Kl3el4HpSKfuiw0gWvEIwMjxytaWlZddaxkZwBQD9ff0n/VLZN1qLl3Nt7mlWu7m88fKudfOuuEtFrJvLm9zTrPZyrjXaSLlU8vt6e0++WESt2RbzxeDg2UtfX7ril33l+75hxczMTEQkEFJgMBEshEBEzMwgwPd9A0G86+iRzigMq+Pj4xNKKSUiolbgZ06f7rlw8eK3uadZTUS87IgAgECIcwyBjRBZgxp2QARYWJCAmImtCPyybw4dPnwwl81lpm5N3VZKKQKAffv27b45PjEdRVGdiFgi4hWLBEIkGl/+/iPuFjMgEHYmt+Krvd14xWmEUQRiBqyFCUNLRByPx0v70+nWO7/decQAMPj5+cFkMlmntdZr4QIBEyGwIR5X5xBHFTUU4MniHMqmChgLf24W5ZkHqD7+EwBYG6Nrk8m6wcHzgwAQa2tre6ujo6OzWCwiFoutOwGy7CIVq4EWjRgxrLWI1dah+MN3mLv2PVBTCxWG2HHmLDZ9dDxW8Dx0HOjobGtru8hdnV1HU6lU7cr5lVXocgFZmq3I6hCxsBCk/CJ2Ogk0NTTgtRgj4ZcgRLDGmFQqVXukq+sot6fTHWEYgplJAMSZ4CiCEQEDqI3Ti6aeuVMxCDEEAmEGlk46iJnCMER6f7oj1tTUtCcMQwhAyRjj/nwFF37NwA81jAgOvrkNn77TAFmx8lyFNX5FVu0yEYVhiKampj3sOE6jiEBAFGfgD9/ibsmBR3WYi1K449Iy/O+dbCASETiO07jaVFpeUEIRNtfEUOsoGGKkEuq/gNeJgyDwiAiy7E8EMGsaajfamn+XEBGCIPA4k8nMJBIJyEuSNpIVkUQigUwmM8MTNyfGEokEZMMuvpzEWkkkEpi4OTHGI6Ojw77vV5hZAQARoGhpJgJ4ubdMhGcPg0AA89IgPHsXgJVSvu9XRkZHh3lycvLe2I2x6/X19TBG68gKypEg1ILFSFCJBAJBxSwitBFC0aiaRRhYYLEKVCtAGAIVHxIG0Nbo+vrNGLsxdn1ycvKeAoCHD2fud3d/0sNAclFb+7hsqDbO2JpUePf1GrRuT2K2Mg8Dg02xFN5I7UB629twimWYggva3ADa/irUewdsfGezMkFQ6jl16sT8k3l3NWgG+gd6REQK2WxUcvM2n3OlkHelUvCkkPfE94qSy+Ukm8tK2SvKQt4Tr1QWz1sQL5sVt7Bgc24hEhEZ6B/oAZZyZjUUpm5N3Y7CqPrhoUMH/cqiiNWGmEnbpe3WYqBIQRFDi1n6frUGIKKtGCVCW7Y0qnOfnTt7+ZvLV1ZyZrXzKzHX19t7slws+UYbcXP55yJzJZOfj8y8NZGWcrHk9/X2dq9lrdP/DP03NoKv+8EopZQ11gpk5drycXu6/YPm5ubdjuM0AEAQBIXZ2dlH4xPjP18bGrr6T9eWvwCrtjIZeT5g2wAAAABJRU5ErkJggg==">
            </a>
            <ul class="nav navbar-nav">
                <li><a id="options_toggle"  data-toggle="modal" data-target="#settingsModal" href="#">Настройки ...</a></li>
            </ul>
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse"  id="bs-example-navbar-collapse-1">
            <form class="navbar-form navbar-left" role="search">
                <div class="form-group">
                    <select id="input_code" name="code" data-placeholder="Тикер или название акции" class="form-control select2-single">
                        <option value=""></option>
<?php foreach (Data::getData() as $aStock) echo '                        <option value="' . $aStock[Data::IDX_CODE] . '"' . ($aStock[Data::IDX_CODE] == $code ? ' selected="selected"' : ''). '>' . '[' . $aStock[Data::IDX_CODE] . '] ' . $aStock[Data::IDX_FULL] . "</option>\n"; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary" id="input_submit">Показать</button>
            </form>
        </div>
    </div>
</nav>

<?php if (count($messages) > 0) { ?>
<div class="container">
    <?php foreach($messages as $msg) { ?><div class="alert alert-<?= $msg['type'] ?> alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <?= $msg['message'] ?>
    </div><?php } ?>
</div>
<?php } ?>

<?php if (strlen($code) == 0 && count($aData) < 1 ) { ?>
<div class="container">
    <h2>Выбрать акцию выше или ввести данные для графика вручную</h2><br/>
    <div class="row">
        <div class="col-sm-8">
            <form method="post">
                <div class="form-group">
                    <label for="input_table">Данные для графика</label>
                    <textarea name="data" id="input_table" class="form-control" rows="10" placeholder=""><?= \Helper\Arr::get($_POST, 'data', '') ?></textarea>
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="reverse" value="true"<?= \Helper\Arr::get($_POST, 'reverse', '') == 'true' ? ' checked="checked"' : '' ?>> Самые "новые" значения вверху таблицы
                    </label>
                </div>
                <button type="submit" class="btn btn-default btn-primary">Построить график</button>
            </form>
        </div>
        <div class="col-sm-4">
            Ожидается либо две колонки "Дата - Цена" разделённые пробелом, либо только колонка "Цена" в столбик (по одному значению в строке).
            И дата и цена не должны содержать в себе пробелов или символов табуляции.
            <h4>Пример данных</h4>
            <pre><?php
                $str  = date('d.m.Y', time() + 86400)     . " 1.03\n";
                $str .= date('d.m.Y', time())             . " 1.02\n";
                $str .= date('d.m.Y', time() - 86400)     . " 1.01\n";
                $str .= date('d.m.Y', time() - 86400 * 2) . " 1\n";
                $str .= date('d.m.Y', time() - 86400 * 3) . " 0.99\n";
                echo $str;
            ?></pre>
        </div>
    </div>
</div>
<?php } ?>

<?php if (count($aData) > 0 ) { ?>

    <div class="container">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_chart" data-toggle="tab">График</a></li>
            <li><a href="#tab_data" data-toggle="tab">Данные</a></li>
            <?php if (strlen($code) > 0) { ?><li><a href="#tab_indicators" data-toggle="tab">Индикаторы</a></li>
            <li><a href="#tab_url" data-toggle="tab">Ссылки</a></li><?php } ?>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane active fade in" id="tab_chart">
            <div id="chart_placeholder"></div>
        </div>
        <div class="container tab-pane fade" id="tab_url">
            <h2><?= $fullText ?></h2>
            <ul>
                <li><a target="_blank" href="https://www.moex.com/ru/issue.aspx?code=<?= $code ?>">Инструмент на сайте МосБиржи</a></li>
                <li><a target="_blank" href="https://ru.tradingview.com/symbols/MOEX-<?= strtoupper($code) ?>">Инструмент на сайте tradingview.com</a></li>
                <li><a target="_blank" href="https://ru.tradingview.com/chart/?symbol=MOEX:<?= strtoupper($code) ?>">График  на сайте tradingview.com</a></li>
                <li><a target="_blank" href="https://www.dohod.ru/ik/analytics/dividend/<?= strtolower($code) ?>/">Информация по дивидендам  (dohod.ru)</a></li>
                <li><a target="_blank" href="https://investmint.ru/<?= strtolower($code) ?>/">Информация по дивидендам (investmint.ru)</a></li>
            </ul>
        </div>
        <div class="container tab-pane fade" id="tab_indicators">
            <h2>Скользящие средние</h2>
            <ul>
                <?php
                //$arSMA = [20,65,100,140/*,280*/];
                $arSMA = [5,9,20,65];
                $arRes = [];
                foreach ($arSMA as $sma) {
                    $arRes[$sma] = $oMoex instanceof \Exchange\Moex ? $oMoex->getSMA($sma) : '';;
                }
                $arRes['last'] = $lastPrice;
                arsort($arRes);
                foreach ($arRes as $key => $value) { ?>
                <li><?= (($key == 'last') ? '<b>Цена</b> = ' : 'SMA(' . $key . ') = ') . $value ?></li>
                <?php } ?>
            </ul>
        </div>
        <div class="container tab-pane fade" id="tab_data">
            <div class="row">
                <div class="col-sm-3">
                    <?php if (count($aData) > 0) { ?><table class="table table-condensed table-striped">
                        <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Цена закрытия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $lastPrice = -1;
                        $rows = [];
                        $className = '';
                        foreach ($aData as $date => $price) {
                            if ($lastPrice > 0) {
                                if ($lastPrice < $price) {
                                    $className = 'color-green';
                                } elseif ($lastPrice > $price) {
                                    $className = 'color-red';
                                } else {
                                    $className = '';
                                }
                            }
                            $sDate =is_string($date) ? date('d.m.Y', strtotime($date)) : '';
                            $rows[] = '<tr><td>' . $sDate . '</td><td class="' . $className . '">' . $price . '</td></tr>';
                            $lastPrice = $price;
                        }
                        echo implode('', array_reverse($rows));
                        ?>
                        </tbody>
                    </table><?php } else { ?><div>Нет данных</div><?php }?>
                </div>
                <div class="col-sm-9"></div>
            </div>
        </div>
    </div>

<?php } ?>

<!-- Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Настройки</h4>
            </div>
            <div class="modal-body">
                <div class="checkbox">
                    <label>
                        <input id="settings_last" type="checkbox"<?= \Helper\Arr::get($_SESSION, 'showLastPrice', $defaultBoolShowLastPrice) ? ' checked="checked"' : '' ?>> Отмечать последнюю цену на графике
                    </label>
                </div>
                <br><br>
                <div class="form-group">
                    <label>Данные за сколько дней использовать для графика
                        <select id="settings_depth" class="form-control" title="Данные за сколько дней использовать для графика">
                            <option value="30"<?= \Helper\Arr::get($_SESSION, 'depth', $defaultIntDepth) == 30 ? ' selected="selected"' : '' ?>>30</option>
                            <option value="50"<?= \Helper\Arr::get($_SESSION, 'depth', $defaultIntDepth) == 50 ? ' selected="selected"' : '' ?>>50</option>
                            <option value="75"<?= \Helper\Arr::get($_SESSION, 'depth', $defaultIntDepth) == 75 ? ' selected="selected"' : '' ?>>75</option>
                            <option value="100"<?= \Helper\Arr::get($_SESSION, 'depth', $defaultIntDepth) == 100 ? ' selected="selected"' : '' ?>>100</option>
                        </select>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" id="settings_save" class="btn btn-primary" data-loading-text="Сохраняем...">Сохранить настройки</button>
            </div>
        </div>
    </div>
</div>


<!-- scripts -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.autocomplete.js"></script>
<script src="js/select2.min.js"></script>
<script>
    var data = <?= json_encode($aData) ?>;
</script>
<script src="js/scripts.js"></script>
</body>
</html>