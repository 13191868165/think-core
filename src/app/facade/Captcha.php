<?php
namespace app\facade;

use think\Facade;

class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\Captcha::class;
    }
}
