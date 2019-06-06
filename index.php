<?php

require_once 'autoloader.php';


$oMoex = new \Exchange\Moex('lkoh');
$data = $oMoex->load(30);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">
    <pre><?= print_r($data, true) ?></pre>
</div>

<!-- scripts -->
<script src="https://cdn.jsdelivr.net/jquery/2.2.4/jquery.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<script src="js/jquery.autocomplete.js"></script>
<script src="js/scripts.js"></script>
</body>
</html>