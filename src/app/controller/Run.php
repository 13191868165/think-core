<?php
namespace app\controller;

use app\controller\admin\Common;
use think\App;

class Run
{
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function run()
    {

        $method = input('method');

        if (empty($method)) {
            throw_exception(10002);
        }

        //处理路由
        $method = preg_replace(['/\/\//', '/\//', '/\.\./'], '.', trim($method, '/'));
        $method = array_filter(explode('.', $method));

        $api = empty($method[0]) ? '' : $method[0];
        $controller = empty($method[1]) ? '' : $method[1];
        $action = empty($method[2]) ? '' : $method[2];
        $method = implode('.', $method);
        $entry = input('_entry', '');

        if (empty($api) || empty($controller) || empty($action)) {
            throw_exception(10002);
        }

        $class = "app\controller\\{$api}\\{$controller}";

        $instance = new $class($this->app, [
            'api' => $api,
            'method' => $method,
            'entry' => $entry,
            'controller' => $controller,
            'action' => $action,
        ]);

        if (!method_exists($instance, $action)) {
            throw_exception(10002);
        }

        return $instance->$action();
    }
}
