<?php
namespace app\facade;

use think\Facade;

/**
 * Class Captcha
 * @package app\facade
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\util\Captcha::class;
    }
}
