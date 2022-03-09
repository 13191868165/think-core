<?php
namespace core\facade;

use think\Facade;

/**
 * Class Captcha
 * @package core\facade
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \core\util\Captcha::class;
    }
}
