<?php
declare (strict_types=1);

namespace app\util;

use Throwable;

/**
 * 自定义异常
 * Class Exception
 * @package app
 */
class Exception extends \RuntimeException
{
    public function __construct($code, $message = "", Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
