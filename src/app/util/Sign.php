<?php
namespace app\util;

/**
 * 签名
 * Class Sign
 * @package app\util
 */
class Sign
{
    /**
     * 检查签名
     * @param $param
     * @param string $key
     * @param string $type
     * @return bool
     */
    public function checkSign($param, $key = '', $type = 'sha1')
    {
        $sign = isset($param['sign']) ? $param['sign'] : '';

        if (empty($sign)) {
            return false;
        }

        $newSign = $this->sign($param, $key, $type);

        if ($sign != $newSign) {
            return false;
        }

        return true;
    }

    /**
     * 签名
     * @param $param
     * @param string $key
     * @param string $type 签名方式
     * @return string
     */
    public function sign($param, $key = '', $type = 'sha1')
    {
        if (isset($param['sign'])) {
            unset($param['sign']);
        }

        $sign = '';
        if ($type == 'sha1') {
            $sign = $this->sha1Sign($param, $key);
        }

        return $sign;
    }

    /**
     * sha1签名
     * @param $param
     * @param $key
     * @return string
     */
    public function sha1Sign($param, $key)
    {
        //数组排序
        ksort($param);
        $str = http_build_query($param);
        return md5(sha1($str) . $key);
    }
}
