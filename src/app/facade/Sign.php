<?php
namespace app\facade;

use think\Facade;

class Sign extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\Sign::class;
    }
}
