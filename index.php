<?php

require_once 'autoloader.php';

$code = \Helper\Arr::get($_GET, 'code', '');
$depth = \Helper\Arr::get($_GET, 'depth', 50);

$fullText='';
$aData = [];
if (strlen($code) > 0) {
    $oMoex = new \Exchange\Moex($code);
    $aData = $oMoex->load($depth);

    $aActive = Data::searchData($code);
    $fullText = '[' . $code . '] ' . $aActive[Data::IDX_FULL];
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $fullText ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <!-- Марка и переключение сгруппированы для лучшего мобильного дисплея -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse">
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
                        <li><a href="#">Очистить избранное</a></li>
                    </ul>
                </li>
            </ul>
            <form class="navbar-form navbar-left" role="search">
                <div class="form-group">
                    <input type="text" class="form-control" value="<?= str_replace('"', '&quot;', $fullText) ?>" placeholder="Акция" id="input_text">
                    <input type="hidden" name="code" value="<?= $code ?>" id="input_code"/>
                </div>
                <div class="form-group">
                    <select  class="form-control" name="depth">
                        <option value="30"<?=  ($depth == 25)  ? ' selected' : '' ?>>30</option>
                        <option value="50"<?=  ($depth == 50)  ? ' selected' : '' ?>>50</option>
                        <option value="75"<?=  ($depth == 75)  ? ' selected' : '' ?>>75</option>
                        <option value="100"<?= ($depth == 100) ? ' selected' : '' ?>>100</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" id="input_submit">Показать</button>
                <a href="/" class="btn btn-default">Очистить</a>
            </form>
        </div>
    </div>
</nav>

<?php if (strlen($code) > 1): ?>

<div class="container">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab_chart" data-toggle="tab">Главная</a></li>
        <li><a href="#tab_data" data-toggle="tab">Данные</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active fade in" id="tab_chart">
            <div id="chart_placeholder">

            </div>
        </div>
        <div class="tab-pane fade" id="tab_data">
            <div class="row">
                <div class="col-sm-3">
                    <table class="table table-condensed table-striped">
                        <thead>
                        <tr>
                            <th>Дата</th>
                            <th>Цена закрытия</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($aData as $date => $price) {
                            echo '<tr><td>' . date('d.m.Y', strtotime($date)) . '</td><td>' . $price . '</td></tr>';
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-sm-9"></div>
            </div>
        </div>
    </div>
</div>

<?php endif; // Если переменная $code есть ?>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/jquery/2.2.4/jquery.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script src="js/jquery.autocomplete.js"></script>
<script>
    var code = "<?= $code ?>";
    var depth = <?= $depth ?>;
</script>
<script src="js/scripts.js"></script>
</body>
</html>