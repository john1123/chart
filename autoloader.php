<?php
function __autoload($className)
{
    $searchDirectories = [
        'chart',
        'lib',
    ];
    foreach($searchDirectories as $directory)
    {
        $fileName = $directory . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className).'.php';
        if (file_exists($fileName)) {
            require_once($fileName);
        }
    }
}