<?php

require_once 'autoloader.php';

$fullText='';
$messages = [];
$aData = [];

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
            $aData[$aParts[0]] = $aParts[1];
        } else {
            $aData[] = $aParts[0];
        }
    }
    if ($isReverse == 'true') {
        $aData = array_reverse($aData);
    }
    $depth = count($aData);
} else {
    $depth = \Helper\Arr::get($_GET, 'depth', 50);
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
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse"  id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Избранное <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="?code=SBER">[SBER] Сбербанк России ПАО ао</a></li>
                        <li><a href="?code=GAZP">[GAZP] "Газпром" (ПАО) ао</a></li>
                        <li><a href="?code=LKOH">[LKOH] НК ЛУКОЙЛ (ПАО) -о</a></li>
                        <li><a href="?code=SBERP">[SBERP] Сбербанк России ПАО ап</a></li>
                        <li><a href="?code=GMKN">[GMKN] ГМК "Нор.Никель" ПАО ао</a></li>
                        <li class="divider"></li>
                        <li><a href="?code=PHOR">[PHOR] ФосАгро ПАОо</a></li>
                        <li><a href="?code=TGKA">[TGKA] ао ПАО "ТГК-1"</a></li>
                        <li><a href="?code=DSKY">[DSKY] ПАО Детский мир</a></li>
                        <li><a href="?code=ALRS">[ALRS] АЛРОСА ПАО ао</a></li>
                        <li><a href="?code=MTLRP">[MTLRP] Мечел ПАО ап</a></li>
                        <li class="divider"></li>
                        <li><a href="javascript:alert('В разработке')">Очистить избранное</a></li>
                    </ul>
                </li>
            </ul>
            <form class="navbar-form navbar-left" role="search">
                <div class="form-group">
                <select id="input_code" name="code" data-placeholder="Тикер или название акции" class="form-control select2-single">
                    <option value=""></option>
<?php foreach (Data::getData() as $aStock) echo '                    <option value="' . $aStock[Data::IDX_CODE] . '"' . ($aStock[Data::IDX_CODE] == $code ? ' selected="selected"' : ''). '>' . '[' . $aStock[Data::IDX_CODE] . '] ' . $aStock[Data::IDX_FULL] . "</option>\n"; ?>
                </select>
                </div>
                <div class="form-group">
                    <select  class="form-control" name="depth" title="Данные за сколько дней использовать для графика">
                        <option value="30"<?=  ($depth == 25)  ? ' selected' : '' ?>>30</option>
                        <option value="50"<?=  ($depth == 50)  ? ' selected' : '' ?>>50</option>
                        <option value="75"<?=  ($depth == 75)  ? ' selected' : '' ?>>75</option>
                        <option value="100"<?= ($depth == 100) ? ' selected' : '' ?>>100</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" id="input_submit">Показать</button>
                <a href="<?= $_SERVER["SCRIPT_NAME"] ?>" class="btn btn-default">Очистить</a>
            </form>
        </div>
    </div>
</nav>

<div class="container">
    <?php if (count($messages) > 0) {
        foreach($messages as $msg) { ?>
            <div class="alert alert-<?= $msg['type'] ?> alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?= $msg['message'] ?>
            </div>
        <?php }
    } ?>
</div>

<?php if (strlen($code) == 0 && count($aData) < 1 ) { ?>
<div class="container">
    <div class="row">
        <div class="row">
            <div class="col-sm-8">
                <form method="post">
                    <div class="form-group">
                        <label for="input_table">Данные для графика</label>
                        <textarea name="data" id="input_table" class="form-control" rows="10" placeholder=""></textarea>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="reverse" value="true" checked="checked"> Самые "новые" значения наверху
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
</div>
<?php } ?>

<?php if (count($aData) > 0 ) { ?>

    <div class="container">
        <ul class="nav nav-tabs">
            <li class="active"><a href="#tab_chart" data-toggle="tab">График</a></li>
            <li><a href="#tab_data" data-toggle="tab">Данные</a></li>
            <li><a href="#tab_indicators" data-toggle="tab">Индикаторы</a></li>
            <li><a href="#tab_url" data-toggle="tab">Ссылки</a></li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane active fade in" id="tab_chart">
            <div class="container">
                <?php try {
                    $oMain = new \Chart\ThreeLinesBreak\Sequence($aData);
                    $aBlocks = $oMain->getBlocks();
                    $oDisplay = new \Chart\ThreeLinesBreak\Display(800,300);
                    $oDisplay->setBlocks($aBlocks);
                    echo $oDisplay->getOutput();
                } catch (\Chart\ChartException $ex) {
                    echo "Ошибка: " . $ex->getMessage();
                } ?>
            </div>
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
                    $arRes[$sma] = $oMoex->getSMA($sma);
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
                            $rows[] = '<tr><td>' . date('d.m.Y', strtotime($date)) . '</td><td class="' . $className . '">' . $price . '</td></tr>';
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

<!-- scripts -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.autocomplete.js"></script>
<script src="js/select2.min.js"></script>
<script>
    var code = "<?= $code ?>";
    var depth = <?= $depth ?>;
</script>
<script src="js/scripts.js"></script>
</body>
</html>