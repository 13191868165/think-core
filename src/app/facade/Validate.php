<?php
namespace app\facade;

use think\Facade;

class Validate extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\Validate::class;
    }
}
