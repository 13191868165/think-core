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
function app_config($name = '', $value = null)
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
        $config = app_config('error');
        $msg = isset($config[$code]) ? $config[$code] : '';
    }
    return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
}

/**
 * core命名空间
 * @param $type
 * @param $name
 * @param bool $new
 * @return string
 */
function app_namespace($type, $name, $new = false)
{
    $class = "\app\\{$type}\\{$name}";
    return $new == true ? new $class() : $class;
}

/**
 * facade命名空间或快速调用静态方法
 * @param $name
 * @param string $fun
 * @return string
 */
function f($name, $fun = '')
{
    $class = app_namespace('facade', $name);
    return $fun ? ($class)::$fun() : $class;
}

/**
 * util命名空间
 * @param $name
 * @param bool $new
 * @return string
 */
function u($name, $new = false)
{
    return app_namespace('util', $name, $new);
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

    $name = ucfirst($args[0]); //模型名称
    if (count($args) == 1) { //实例化
        $_modules[$name] = app_namespace('model', $name, true);
    } elseif (count($args) == 2 && $args[1] === true) { //命名空间
        return app_namespace('model', $name);
    } else { //实例化并传参
        $class = app_namespace('model', $name);
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
