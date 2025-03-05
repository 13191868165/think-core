<?php
namespace app\facade;

use think\Facade;

class Exception extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\Exception::class;
    }
}
