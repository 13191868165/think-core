<?php
namespace app\core\facade;

use think\Facade;

class CoreConfig extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\core\util\CoreConfig::class;
    }
}
