<?php
namespace app\core\util;

class Test
{
    public function hello($str = '')
    {
        return __CLASS__ . '_' . __FUNCTION__ . $str;
    }
}
