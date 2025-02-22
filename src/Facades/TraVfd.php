<?php

namespace Taitech\TravfdPhp\Facades;

use Illuminate\Support\Facades\Facade;

class Travfd extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'travfd';
    }
}