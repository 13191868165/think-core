<?php
namespace app\core\util;

class Str extends \think\helper\Str
{
    public function sign($param, $sign_key = '')
    {
        //数组排序
        ksort($param);
        $str = http_build_query($param);
        return md5(sha1($str) . $sign_key);
    }
}
