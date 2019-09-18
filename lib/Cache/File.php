<?php

namespace Cache;

class File extends \Base implements ICache
{
    protected $aOptions;

    public function __construct(array $aOptions=[]) {
        parent::__construct($aOptions);

        $cacheDirectory = $this->getOption('cacheDirectory', 'cache');

        // Пытаемся создать папку для кеша
        if(!is_dir($cacheDirectory)){
            mkdir($cacheDirectory, 0777);
        }
        // Если директории по прежнему нет - ошибка
        if (!is_dir($cacheDirectory) || !is_writable($cacheDirectory)) {
            throw new \Exception('Папки ' . $cacheDirectory . ' не существует или нельзя писать в неё.');
        }
    }

    public function get($name, $default='') {
        $fileName = $this->getOption('cacheDirectory', 'cache') . DIRECTORY_SEPARATOR
            . strtolower($name) . '.' . $this->getOption('cacheExtension', 'json');
        $refresh = $this->getOption('cacheRefresh', 'false');
        if ($refresh == 'true') {
            @unlink($fileName);
        }
        $result = $default;
        if (file_exists($fileName)) {
            $result = file_get_contents($fileName);
//            $fileDate = date('Y-m-d H:i:s', filectime($fileName));
//            $currDate = date('Y-m-d 20:00:00');
//            if ($fileDate < $currDate) {
//                @unlink($fileName);
//            }
        }
        return $result;
    }

    public function set($name, $data) {
        $fileName = $this->getOption('cacheDirectory', 'cache') . DIRECTORY_SEPARATOR
            . strtolower($name) . '.' . $this->getOption('cacheExtension', 'json');
        @file_put_contents($fileName, $data);
    }

}