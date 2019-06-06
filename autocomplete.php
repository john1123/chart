<?php

require_once('autoloader.php');

$result = [];
$searchString = $_GET['search'];
if (strlen($searchString) > 0) {
    $data = Data::getData();
    $result = [];
    foreach ($data as $stock) {
        if (
                stripos($stock[Data::IDX_SHORT], $searchString) !== false
             || stripos($stock[Data::IDX_FULL], $searchString) !== false
             || stripos($stock[Data::IDX_CODE], $searchString) !== false
        ) {
            $result[] = [
                'code' => $stock[Data::IDX_CODE],
                'value' => '[' . $stock[Data::IDX_CODE] . '] ' . $stock[Data::IDX_FULL],
            ];
        }
    }
}
$data = ['suggestions' => $result];
header('Content-Type: application/json');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
