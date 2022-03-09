<?php
namespace core\facade;

use think\Facade;

/**
 * Class CaptchaApi
 * @package core\facade
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \core\util\Captcha::class;
    }
}
