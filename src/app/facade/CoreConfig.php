<?php
namespace app\facade;

use think\Facade;

class CoreConfig extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\CoreConfig::class;
    }
}
