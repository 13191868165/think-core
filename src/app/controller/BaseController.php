<?php
declare (strict_types=1);

namespace app\controller;

use think\App;

/**
 * 控制器基础类
 * Class BaseController
 * @package app\controller
 */
abstract class BaseController
{
    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 接口名称
     * @var
     */
    protected $method;

    /**
     * api入口：admin api
     * @var
     */
    protected $api;

    /**
     * 控制器
     * @var
     */
    protected $controller;

    /**
     * 方法
     * @var
     */
    protected $action;

    /**
     * 处于开发模式 是：true 否：false
     * @var
     */
    protected $isDev;

    /**
     * 入口类型
     * 管理端：admin(1)、merchant(2)
     * 用户端：mobile[wap(11)、wechat(12)]
     *       applet[wxapp(21)、ttapp(22)]
     *       app[android(31)、ios(32)]
     *       api[api(41)、adminApi(42)]
     *       web[pc(51)、admin(52)、merchant(53)]
     * @var
     */
    protected $_entry;

    /**
     * 应用信息
     * @var
     */
    protected $appInfo;

    /**
     * 用户信息
     * @var array
     */
    protected $user = [];

    /**
     * 管理员类型
     * @var int
     */
    protected $adminType = 0;

    /**
     * 构造方法
     * BaseController constructor.
     * @param App $app 应用对象
     * @param array $route
     * @throws \ReflectionException
     */
    public function __construct(App $app, $route = [])
    {
        // 初始化路由
        $this->initRoute($route);

        // 初始化 应用对象、request请求
        $this->app = $app;
        $this->request = $this->app->request;

        // 开发设置
        $this->bindAppConfig();

        // 打开跨域
        /*跨域功能此位置不生效，移到了中间件*/
        /*$this->accessCors();*/

        // 校验应用规则
        $this->checkAppRule();

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化路由
     * @param $route
     * @return void
     * @throws \ReflectionException
     */
    private function initRoute($route)
    {
        // 设置接口名称
        if (isset($route['method'])) {
            $this->method = $route['method'];
        }
        // 设置api入口：admin api
        if (isset($route['api'])) {
            $this->api = $route['api'];
        }
        // 设置控制器
        if (isset($route['controller'])) {
            $this->controller = $route['controller'];
        }
        // 设置方法
        if (isset($route['action'])) {
            $this->action = $route['action'];
        }
        if (
            empty($this->method) ||
            empty($this->api) ||
            empty($this->controller) ||
            empty($this->action) ||
            ($this->api != 'admin' && $this->api != 'api')
        ) {
            throw_exception(10002);
        }

        // 设置入口类型
        $_entry = input('_entry', 0);
        if (!empty($_entry)) {
            $this->_entry = $_entry;

            // 检查entryType是否存在
            $entryType = m('app')->enumeration['entryType'][$this->api];
            if (!empty($entryType)) {
                if (empty($entryType[$this->_entry])) {
                    throw_exception(10002);
                }
            }
        }
    }

    /**
     * 绑定应用配置
     */
    private function bindAppConfig()
    {
        // 绑定主要配置
        set_config(get_config($this->api), $this->api, true);
        // 绑定开发配置
        set_config(get_config("development.{$this->api}"), 'development', true);

        $whitelist = get_config("whitelist");

        // 绑定app白名单
        $appWhitelist = $whitelist['app_whitelist'][$this->api] ?? [];
        set_config($appWhitelist, 'app_whitelist', true);

        $loginWhitelist = $whitelist['login_whitelist'][$this->api] ?? [];
        // 绑定登录白名单
        set_config($loginWhitelist, 'login_whitelist', true);
    }

    /**
     * 打开跨域
     */
    /*protected function accessCors()
    {
        header('Content-Type: *');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // 设置允许访问的协议
        header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
        header('Access-Control-Allow-Headers: *');
        header('Content-Type:text/html; charset=utf-8'); // 响应类型
    }*/

    /**
     * 校验应用规则
     * @throws \ReflectionException
     */
    protected function checkAppRule()
    {
        $api = $this->api;

        // 校验app白名单
        if (checkWhitelist(config('app_whitelist', []), $this->method)) {
            return false;
        }

        // 请求方式，0:header 1:input
        if (!empty($this->_entry) && ($this->_entry == 41 || $this->_entry == 42)) {
            $appid = input('appid', '');
            $requestType = 1;
        } else {
            $appid = $this->request->header('x-access-appid', '');
            $requestType = 0;
        }

        // 检查是否开启调试模式
        if (empty($appid)) {
            $dev = config("development", []);
            if (
                $dev['debug'] == true &&
                !empty($dev['salt']) &&
                $dev['salt'] == input('_salt', '', 'trim')
            ) {
                $appid = $dev['appid'];
                $this->isDev = true;
            }
        }

        // 安装模块
        if ($this->method === 'admin.install.install') {
            if (!$this->isDev) {
                throw_exception(10002);
            }

            if (!m('install')->isInstall()) {
                m('Install')->install();
                return false;
            } else {
                throw_exception(10002);
            }
        }

        // 检查令牌是否存在
        if (empty($appid)) {
            throw_exception(10100);
        }

        // 获取应用信息
        $appInfo = m('app')->getRow(['appid' => $appid]);

        // 校验应用数据
        if (empty($appInfo) || $appInfo['is_enabled'] == 0) {
            throw_exception(10101);
        }

        // 校验请求方式
        if ($requestType !== $appInfo['request_type']) {
            throw_exception(10002);
        }

        // 校验签名盐
        if (empty($appInfo['secret'])) {
            throw_exception(10103);
        }

        // 校验应用入口类型
        if (intval($appInfo[$api]) != 1) {
            throw_exception(10101);
        }

        // 接口调用有效期

        // 校验签名
        $dev = config('development');
        if (!($this->isDev == true && $dev['check_sign'] == false)) {
            $input = input();
            //处理上传功能验签
            if ($this->api == 'admin' && $this->controller == 'Upload') {
                unset($input['file']);
            }
            if (f('Sign')::checkSign($input, $appInfo['secret']) != true) {
                throw_exception(10104);
            }
        }

        $this->appInfo = $appInfo;
    }

    /**
     * 获取枚举数据
     * @return \think\response\Json|void
     * @throws \ReflectionException
     */
    protected function getModelEnumeration()
    {
        $data = input('data', '');

        if (empty($data)) {
            return show_json(10004);
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (class_exists(app_namespace('model', $key)) == true) {
                $enumeration = m($key)->enumeration;
                if (!empty($enumeration)) {
                    foreach ($value as $val) {
                        $result[$key][$val] = isset($enumeration[$val]) ? $enumeration[$val] : null;
                    }
                }
            }
        }

        return show_json(0, '操作成功', $result);
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
    }

    protected function _initialize()
    {
        // 初始化模型名称
        $name = str_replace('\\', '/', static::class);
        $this::$model = basename($name);

        // 校验登录白名单
        if (checkWhitelist(config('login_whitelist', []), $this->method) == true) {
            return true;
        }

        // 开发调试模式
        if ($this->isDev) {
            $token = input('token') ? input('token') : config('development.token');
        } else {
            if ($this->appInfo['request_type'] === 0) {
                $token = $this->request->header('x-access-token', '');
            } else {
                $token = input('token', '');
            }
        }

        // 检查token并设置用户信息
        $this->checkSetUser($token);
    }
}
