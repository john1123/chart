<?php

abstract class Base
{
    protected $aOptions;

    public function __construct($ticker, array $aOptions=[])
    {
        $this->setOptions($aOptions);
    }

    protected function setOptions(array $aOptions)
    {
        $this->aOptions = $aOptions;
    }

    protected function getOption($name, $default='')
    {
        return Helper\Arr::get($this->aOptions, $name, $default);
    }

}