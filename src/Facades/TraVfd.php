<?php

namespace Taitech\TravfdPhp\Facades;

use Illuminate\Support\Facades\Facade;

class TraVfd extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tra-vfd';
    }
}