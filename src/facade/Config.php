<?php
namespace core\facade;

use think\Facade;

class Config extends Facade
{
    protected static function getFacadeClass()
    {
        return \core\util\Config::class;
    }
}
