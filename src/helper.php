<?php

/**
 * 使用pre标签打印
 */
function print_pre()
{
    $args = func_get_args();
    foreach ($args as $arg) {
        echo '<pre>' . print_r($arg, true) . '</pre>';
    }
}

/**
 * print_pre 别名
 */
if (!function_exists('debug')) {
    function debug()
    {
        call_user_func_array('print_pre', func_get_args());
    }
}

/**
 * 系统错误(抛出异常)
 * @param $code
 * @param string $message
 * @param Throwable|null $previous
 */
function throw_exception($code, $message = "", Throwable $previous = null)
{
    throw new \app\core\util\Exception($code, $message, $previous);
}

/**
 * 应用core目录
 * @param string $path
 * @param $isCore
 * @return string
 */
function core_path($path = '', $isCore = false)
{
    if (is_bool($path)) {
        $isCore = $path;
        $path = '';
    }
    if ($isCore == true) {
        $path =  __DIR__ . DIRECTORY_SEPARATOR . ($path ? $path . DIRECTORY_SEPARATOR : '');
    } else {
        $path = app_path() . 'core' . DIRECTORY_SEPARATOR . ($path ? $path . DIRECTORY_SEPARATOR : '');
    }

    return $path;
}

/**
 * 获取和设置core配置文件参数
 * @param string|array $name 参数名
 * @param mixed $value 参数值
 * @return mixed
 */
function core_config($name = '', $value = null)
{
    return \app\core\facade\CoreConfig::get($name, $value);
}

/**
 * 设置配置文件
 * @param $config
 * @param $name
 * @param bool $setConfig
 * @return mixed
 */
function set_config($config, $name, $setConfig = false)
{
    return \app\core\facade\CoreConfig::set($config, $name, $setConfig);
}

/**
 * @param $code
 * @param string $msg
 * @param array $data
 * @return \think\response\Json
 */
function show_json($code, $msg = '', $data = [])
{
    if (empty($msg) && $code >= 10000) {
        $config = core_config('error');
        $msg = isset($config[$code]) ? $config[$code] : '';
    }
    return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
}

/**
 * 实例化模型
 * @return mixed
 * @throws ReflectionException
 */
function m()
{
    static $_modules = [];

    $args = func_get_args();
    $name = ucfirst(array_shift($args));

    if (isset($_modules[$name])) {
        return $_modules[$name];
    }

    $class = "\app\core\model\\{$name}";
    if (empty($args)) {
        $_modules[$name] = new $class();
    } else {
        $reflection = new ReflectionClass($class);
        $_modules[$name] = $reflection->newInstanceArgs($args);
    }

    return $_modules[$name];
}

/**
 * 表名
 * @param $name
 * @param bool $usePrefix
 * @return string
 */
function table($name, $usePrefix = true)
{
    return $usePrefix ? config('database.connections.mysql.prefix') . $name : $name;
}




//u util
//f facade
//c config
//u util
//f facade
//c config
//
///**
// * 是否为空
// * @param $data
// * @return bool
// */
//function isEmpty($data) {
//    $type = gettype($data);
//    if($type == 'array') {
//        return empty($data);
//    }else{
//        return (!isset($data) || $data === '' || $data === null) ? true : false;
//    }
//}
//
//
///**
// * 创建hash加密串
// * @param $password
// * @param string $salt
// * @return string
// */
//function create_hash($password, $salt = '')
//{
//    if ($password == '') {
//        return '';
//    }
//    $authkey = core_config('setting.authkey');
//    return sha1(md5("{$password}-{$salt}-") . $authkey);
//}
//
///**
// * 生成指定长度随机字符串
// * @param $length
// * @return string
// */
//function random($length)
//{
//    return \think\helper\Str::random($length);
//}
//
///**
// * 驼峰转下划线
// * @param $value
// * @param string $delimiter
// * @return false|mixed|string|string[]|null
// */
//function str_snake($value, $delimiter = '_')
//{
//    static $snakeCache = [];
//
//    $key = $value;
//    if (isset($snakeCache[$key][$delimiter])) {
//        return $snakeCache[$key][$delimiter];
//    }
//
//    if (!ctype_lower($value)) {
//        $value = preg_replace('/\s+/u', '', $value);
//        $value = mb_strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value), 'UTF-8');
//    }
//
//    return $snakeCache[$key][$delimiter] = $value;
//}
//
///**
// * 下划线转驼峰(首字母小写)
// * @param $value
// * @return mixed|string
// */
//function str_camel($value)
//{
//    static $camelCache = [];
//
//    if (isset($camelCache[$value])) {
//        return $camelCache[$value];
//    }
//
//    return $camelCache[$value] = lcfirst(str_studly($value));
//}
//
///**
// * 下划线转驼峰(首字母大写)
// * @param $value
// * @return mixed|string|string[]
// */
//function str_studly($value)
//{
//    static $studlyCache = [];
//
//    $key = $value;
//    if (isset($studlyCache[$key])) {
//        return $studlyCache[$key];
//    }
//
//    $value = ucwords(str_replace(['-', '_'], ' ', $value));
//
//    return $studlyCache[$key] = str_replace(' ', '', $value);
//}
//
///**
// * 格式化价格
// * @param $money
// * @param int $len
// * @param string $separator
// * @param bool $abs
// * @return string
// */
//function format_price($money, $len = 2, $separator = '', $abs = true)
//{
//    if ($abs) {
//        $money = abs($money);
//    }
//    $len = intval($len);
//    return number_format($money, $len, '.', $separator);
//}
//
///**
// * 获取ip
// * @return mixed|string
// */
//function getip()
//{
//    static $ip = '';
//    $ip = $_SERVER['REMOTE_ADDR'];
//    if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
//        $ip = $_SERVER['HTTP_CDN_SRC_IP'];
//    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
//        $ip = $_SERVER['HTTP_CLIENT_IP'];
//    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
//        foreach ($matches[0] AS $xip) {
//            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
//                $ip = $xip;
//                break;
//            }
//        }
//    }
//    if (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) {
//        return $ip;
//    } else {
//        return '127.0.0.1';
//    }
//}
//

//
///**
// * 系统加密方法
// * @param string $data 要加密的字符串
// * @param string $key 加密密钥
// * @param int $expire 过期时间 单位 秒
// * @return string
// */
//function think_encrypt($data, $key = '', $expire = 0)
//{
//    $key = md5(empty($key) ? core_config('system.authkey') : $key);
//    $data = base64_encode($data);
//    $x = 0;
//    $len = strlen($data);
//    $l = strlen($key);
//    $char = '';
//
//    for ($i = 0; $i < $len; $i++) {
//        if ($x == $l) $x = 0;
//        $char .= substr($key, $x, 1);
//        $x++;
//    }
//
//    $str = sprintf('%010d', $expire ? $expire + time() : 0);
//
//    for ($i = 0; $i < $len; $i++) {
//        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
//    }
//
//    $str = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
//    return strtoupper(md5($str)) . $str;
//}
//
///**
// * 系统解密方法
// * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
// * @param string $key 加密密钥
// * @return string
// */
//function think_decrypt($data, $key = '')
//{
//    $key = md5(empty($key) ? core_config('system.authkey') : $key);
//    $data = substr($data, 32);
//    $data = str_replace(array('-', '_'), array('+', '/'), $data);
//    $mod4 = strlen($data) % 4;
//    if ($mod4) {
//        $data .= substr('====', $mod4);
//    }
//    $data = base64_decode($data);
//    $expire = substr($data, 0, 10);
//    $data = substr($data, 10);
//
//    if ($expire > 0 && $expire < time()) {
//        return '';
//    }
//    $x = 0;
//    $len = strlen($data);
//    $l = strlen($key);
//    $char = $str = '';
//
//    for ($i = 0; $i < $len; $i++) {
//        if ($x == $l) $x = 0;
//        $char .= substr($key, $x, 1);
//        $x++;
//    }
//
//    for ($i = 0; $i < $len; $i++) {
//        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
//            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
//        } else {
//            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
//        }
//    }
//    return base64_decode($str);
//}
