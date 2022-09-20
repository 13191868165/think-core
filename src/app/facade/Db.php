<?php
namespace app\facade;

use think\Facade;

class Db extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\db\Db::class;
    }
}
