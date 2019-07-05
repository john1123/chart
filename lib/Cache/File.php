<?php

namespace Cache;

class File extends \Base implements ICache
{
    protected $aOptions;

    public function __construct(array $aOptions=[]) {
        parent::__construct($aOptions);
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
            $fileDate = date('Y-m-d H:i:s', filectime($filename));
            $currDate = date('Y-m-d 20:00:00');
            if ($fileDate < $currDate) {
                @unlink($fileName);
            }

            $result = file_get_contents($fileName);
        }
        return $result;
    }

    public function set($name, $data) {
        $fileName = $this->getOption('cacheDirectory', 'cache') . DIRECTORY_SEPARATOR
            . strtolower($name) . '.' . $this->getOption('cacheExtension', 'json');
        @file_put_contents($fileName, $data);
    }

}