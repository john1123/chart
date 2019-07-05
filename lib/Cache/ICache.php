<?php

namespace Cache;

interface ICache
{
    public function get($name, $default);
    public function set($name, $data);
}