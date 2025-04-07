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
function get_config($name = '', $value = null)
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
        $config = get_config('error');
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
 * @param string $type
 * @return string
 */
function table($name, $type = 'mysql')
{
    return empty($type) ? $name : config("database.connections.{$type}.prefix") . $name;
}

function getRoute($method)
{
    $route = preg_replace(['/\/\//', '/\//', '/\.\./'], '.', trim($method, '/'));
    $route = array_filter(explode('.', $route));
    return $route;
}

/**
 * 检查路由
 * @return void
 */
function checkWhitelist($list, $method)
{
    $method = explode('.', strtolower($method));

    $result = false;
    if (count($list) > 0 && count($method) == 3) {
        foreach ($list as &$value) {
            $value = explode('.', strtolower($value));
            if (count($value) != 3) {
                break;
            }
            if ($method[0] == $value[0]
                && $method[1] == $value[1]
                && ($method[2] == $value[2] || $value[2] === '*')) {
                $result = true;
                break;
            }
        }
        unset($value);
    }

    return $result;
}

/**
 * 输出日志
 * @return void
 */

function outlog()
{
    $args = func_get_args();
    $file = array_pop($args);

    $time = date('Y-m-d H:i:s');
    $message = ["时间：{$time}，内容：\n"];
    if (count($args) > 0) {
        foreach ($args as $value) {
            $message[] = (is_array($value) || is_object($value))
                ? var_export($value, TRUE)
                : $value;
        }
    }
    $message[] = "\n";
    $message = join($message);
    $path = runtime_path() . $file;
    $dir = dirname($path);
    if (!file_exists($dir)) {
        @mkdir($dir, 0777, true);
    }
    error_log($message, 3, $path);
}
