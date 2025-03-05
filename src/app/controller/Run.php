<?php
namespace app\controller;

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
        $method = getRoute($method);
        if (count($method) !== 3) {
            throw_exception(10002);
        }
        $api = $method[0];
        $controller = $method[1];
        $action = $method[2];
        $method = implode('.', $method);

        if (empty($api) || empty($controller) || empty($action)) {
            throw_exception(10002);
        }

        $class = "app\controller\\{$api}\\{$controller}";
        if (!class_exists($class)) {
            $controller = f('Str')::studly($controller);
            $class = "app\controller\\{$api}\\{$controller}";
        }

        $instance = new $class($this->app, [
            'api' => $api,
            'method' => $method,
            'controller' => $controller,
            'action' => $action,
        ]);

        //$action = f('Str')::camel($action);
        if (!method_exists($instance, $action)) {
            throw_exception(10002);
        }

        return $instance->$action();
    }
}
