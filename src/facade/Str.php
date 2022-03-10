<?php
namespace app\core\facade;

use think\Facade;

class Str extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\core\util\Str::class;
    }
}
