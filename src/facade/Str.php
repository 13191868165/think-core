<?php
namespace core\facade;

use think\Facade;

class Str extends Facade
{
    protected static function getFacadeClass()
    {
        return \core\util\Str::class;
    }
}
