<?php
namespace app\util;

class Str extends \think\helper\Str
{

    /**
     * 是否为空
     * @param $data
     * @return bool
     */
    public function isEmpty($data)
    {
        $type = gettype($data);
        if ($type == 'array') {
            return empty($data);
        } else {
            return (!isset($data) || $data === '' || $data === null) ? true : false;
        }
    }

    /**
     * 签名
     * @param $param
     * @param string $sign_key
     * @return string
     */
    public function sign($param, $sign_key = '')
    {
        //数组排序
        ksort($param);
        $str = http_build_query($param);
        return md5(sha1($str) . $sign_key);
    }

    /**
     * 创建hash加密串
     * @param $password
     * @param string $salt
     * @return string
     */
    public function createHash($password, $salt = '')
    {
        if ($password == '') {
            return '';
        }
        $authkey = core_config('setting.authkey');
        return sha1(md5("{$password}-{$salt}-") . $authkey);
    }

    /**
     * 格式化价格
     * @param $money
     * @param int $len
     * @param string $separator
     * @param bool $abs
     * @return string
     */
    public function format_price($money, $len = 2, $separator = '', $abs = true)
    {
        if ($abs) {
            $money = max(0, ($money * 1));
        }
        $len = intval($len);
        return number_format($money, $len, '.', $separator);
    }

    /**
     * 获取ip
     * @return mixed|string
     */
    public function getip()
    {
        static $ip = '';
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) {
            return $ip;
        } else {
            return '127.0.0.1';
        }
    }



}
