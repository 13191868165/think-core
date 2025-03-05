<?php
namespace app\facade;

use think\Facade;

class Jwt extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\Jwt::class;
    }
}
