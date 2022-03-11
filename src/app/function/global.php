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
    $Exception = u('Exception');
    throw new $Exception($code, $message, $previous);
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
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . ($path ? $path . DIRECTORY_SEPARATOR : '');
    } else {
        $path = app_path() . ($path ? $path . DIRECTORY_SEPARATOR : '');
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
    return f('CoreConfig')::get($name, $value);
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

    return f('CoreConfig')::set($config, $name, $setConfig);
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
 * core命名空间
 * @param $type
 * @param $name
 * @return string
 */
function core_namespace($type, $name)
{
    return "\app\\{$type}\\{$name}";
}

/**
 * facade命名空间或快速调用静态方法
 * @param $name
 * @param string $fun
 * @return string
 */
function f($name, $fun = '')
{
    $class = core_namespace('facade', $name);
    return $fun ? ($class)::$fun() : $class;
}

/**
 * util命名空间
 * @param $name
 * @return string
 */
function u($name)
{
    return core_namespace('util', $name);
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
    if (empty($args)) {
        return false;
    }

    $name = ucfirst($args[0]);

    if (isset($_modules[$name])) {
        return $_modules[$name];
    }

    $class = core_namespace('model', $name);
    if (count($args) == 1) {
        $_modules[$name] = new $class();
    } elseif (count($args) == 2 && $args[1] === true) {
        return $class;
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
