<?php
namespace app\core\facade;

use think\Facade;

class Test extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\core\Test::class;
    }
}
