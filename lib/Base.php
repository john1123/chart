<?php

abstract class Base
{
    protected $aOptions;
    protected $aObjects;

    const TYPE_INDICATOR = 'indicator';

    public function __construct(array $aOptions=[])
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

    /**
     * Фабрика объектов. Создаёт объект и сохраняет его для дальнейшего использования
     */
    protected function create($type, array $aParamerets=array())
    {
        $object = null;
        switch ($type) {
            case self::TYPE_INDICATOR:
                $subType = Helper\Arr::cut($aParamerets, 'type');
                if (!in_array($subType, array('SMA', 'EMA'))) {
                    throw new Exception('Ошибка создания индикатора. Неизвестный тип: ' . $subType);
                }
                $object = new $type($aParamerets);

                // сохраняем созданный объект
                $this->aObjects[self::TYPE_INDICATOR][$type] = $object;
                break;
            default:
                throw new Exception('Ошибка создания объекта. Неизвестный тип: ' . $type);
        }
        return $object;
    }

}