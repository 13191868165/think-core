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
     * 创建hash加密串
     * @param $str
     * @param string $salt
     * @return string
     */
    public function createHash($str, $salt = '')
    {
        if ($str == '') {
            return '';
        }
        $authkey = core_config('setting.authkey');
        return sha1(md5("{$str}-{$salt}-") . $authkey);
    }

    /**
     * 格式化价格
     * @param $money
     * @param int $len
     * @param string $separator
     * @param bool $abs
     * @return string
     */
    public function priceFormat($money, $len = 2, $separator = '', $abs = true)
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

    /**
     * 加密方法
     * @param string $data 要加密的字符串
     * @param string $key 加密密钥
     * @param int $expire 过期时间 单位 秒
     * @return string
     */
    public function encrypt($data, $key = '', $expire = 0)
    {
        $key = md5(empty($key) ? core_config('admin.authkey') : $key);
        $data = base64_encode($data);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        $str = sprintf('%010d', $expire ? $expire + time() : 0);

        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
        }

        $str = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
        return strtoupper(md5($str)) . $str;
    }

    /**
     * 系统解密方法
     * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
     * @param string $key 加密密钥
     * @return string
     */
    public function decrypt($data, $key = '')
    {
        $key = md5(empty($key) ? core_config('admin.authkey') : $key);
        $data = substr($data, 32);
        $data = str_replace(array('-', '_'), array('+', '/'), $data);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        $data = base64_decode($data);
        $expire = substr($data, 0, 10);
        $data = substr($data, 10);

        if ($expire > 0 && $expire < time()) {
            return '';
        }
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = $str = '';

        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) $x = 0;
            $char .= substr($key, $x, 1);
            $x++;
        }

        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return base64_decode($str);
    }

    /**
     * 数据脱敏
     * @param $str
     * @param int $start 开始位置
     * @param int $len 长度
     * @param string $separator 符号
     * @param int $sepLen 符号长度
     * @return string
     */
    public function dataMasking($str, $start = 0, $len = 0, $separator = '*', $sepLen = 0)
    {
        $start = max(0, $start);
        $len = max(0, $len);

        $arr = [];
        if (!empty($str)) {
            $strlen = mb_strlen($str);

            if ($len == 0) {
                $end = $strlen;
            } else {
                $end = min($strlen, $start + $len);
            }

            for ($i = 0; $i < $strlen; $i++) {
                if ($i >= $start && $i < $end) {
                    if($sepLen == 0 || ($sepLen > 0 && $i >= ($end - $sepLen))) {
                        $arr[$i] = $separator;
                    }
                } else {
                    $arr[$i] = mb_substr($str, $i, 1, 'utf8');
                }
            }
        }

        return implode('', $arr);
    }

}
