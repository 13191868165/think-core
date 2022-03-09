<?php
namespace app\core\facade;

use think\Facade;

/**
 * Class Captcha
 * @package app\core\facade
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \app\core\util\Captcha::class;
    }
}
