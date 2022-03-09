<?php
namespace core\facade;

use think\Facade;

class CoreConfig extends Facade
{
    protected static function getFacadeClass()
    {
        return \core\util\CoreConfig::class;
    }
}
