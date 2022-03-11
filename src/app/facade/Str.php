<?php
namespace app\facade;

use think\Facade;

class Str extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\Str::class;
    }
}
