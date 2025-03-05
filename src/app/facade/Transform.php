<?php
namespace app\facade;

use think\Facade;

class Transform extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\Transform::class;
    }
}
