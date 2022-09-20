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
     * 入口原始数据
     * @var
     */
    protected $entry;

    /**
     * 入口类型，管理端：admin、merchant
     *         用户端：mobile[wap、wechat]
     *                applet[wxapp、ttapp]
     *                app[android、ios]
     *                api[api、adminApi]
     *                web[pc、admin、merchant]
     * @var
     */
    protected $entryType;

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
     * 构造方法
     * BaseController constructor.
     * @param App $app 应用对象
     * @param array $route
     * @throws \ReflectionException
     */
    public function __construct(App $app, $route = [])
    {
        //初始化路由
        $this->initRoute($route);

        //初始化 应用对象、request请求
        $this->app = $app;
        $this->request = $this->app->request;

        //开发设置
        $this->bindAppConfig();

        //打开跨域
        /*跨域功能此位置不生效，移到了中间件*/
        /*$this->accessCors();*/

        //校验应用规则
        $this->checkAppRule();

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     */
    protected function initialize()
    {
    }

    protected function _initialize() {
        //初始化模型名称
        $name = str_replace('\\', '/', static::class);
        $this::$model = basename($name);

        //校验登录白名单
        if ($this->checkLoginWhitelist() == true) {
            return true;
        }

        $dev = config('development');
        //开发调试模式
        $token = $this->appInfo['request_type'] === 0 ? $this->request->header('x-access-token', '') : input('token', '');;
        if (empty($token)) {
            if ($dev['debug'] == true) {
                $token = $dev['token'];
            } else {
                throw_exception(10209);
            }
        }

        //校验用户访问令牌
        $user = $this->checkToken($token);
        //设置用户信息
        $this->user = $user;
    }

    /**
     * @param $route
     * @return void
     * @throws \ReflectionException
     */
    private function initRoute($route)
    {
        if (isset($route['method'])) {
            $this->method = $route['method'];
        }
        if (isset($route['api'])) {
            $this->api = $route['api'];
        }

        if (isset($route['entry'])) {
            $this->entry = $route['entry'];
            //处理entryType
            if (!empty($this->entry)) {
                if ($this->api == 'admin') {
                    $this->entryType = array_search($this->entry, m('admin')->entryTypeData);
                    if (empty($this->entryType)) {
                        throw_exception(10002);
                    }
                } else {
                    $this->entryType = explode('.', $this->entry);
                    if (count($this->entryType) !== 2) {
                        throw_exception(10002);
                    }
                    $entryTypeData = m('user')->entryTypeData;
                    [$source, $system] = $this->entryType;
                    if (empty($entryTypeData[$source]) || empty($entryTypeData[$source]['list'][$system])) {
                        throw_exception(10002);
                    }
                }
            }
        }
        if (isset($route['controller'])) {
            $this->controller = $route['controller'];
        }
        if (isset($route['action'])) {
            $this->action = $route['action'];
        }

        if (empty($this->method) || empty($this->api) || ($this->api != 'admin' && $this->api != 'api')) {
            throw_exception(10002);
        }
    }

    /**
     * 绑定应用配置
     */
    private function bindAppConfig()
    {
        //绑定主要配置
        set_config(get_config($this->api), $this->api, true);
        //绑定开发配置
        set_config(get_config("development.{$this->api}"), 'development', true);
        //绑定app白名单
        set_config(get_config("whitelist.app_whitelist.{$this->api}"), 'app_whitelist', true);
        //绑定登录白名单
        set_config(get_config("whitelist.login_whitelist.{$this->api}"), 'login_whitelist', true);
    }

    /**
     * 打开跨域
     */
    protected function accessCors()
    {
        header('Content-Type: *');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');//设置允许访问的协议
        header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
        header('Access-Control-Allow-Headers: *');
        header('Content-Type:text/html; charset=utf-8');//响应类型
    }

    /**
     * 校验应用规则
     * @throws \ReflectionException
     */
    protected function checkAppRule()
    {
        $api = $this->api;

        $appWhitelist = config('app_whitelist');
//        $checkRoute = checkRoute($appWhitelist, $this->method);
        $checkRoute = checkRoute('222', 'test');
        var_dump($checkRoute);
        exit;

        //请求方式，0:header 1:input
        if ($this->entry == 'api.api' || $this->entry == 'api.adminApi') {
            $appid = input('appid', '');
            $requestType = 1;
        } else {
            $appid = request()->header('x-access-appid', '');
            $requestType = 0;
        }

        //检查是否开启调试模式
        if (empty($appid)) {
            $development = config("development", []);
            if (!empty($development['debug'])
                && !empty($development['salt'])
                && $development['salt'] == input('_salt', '', 'trim')) {
                $appid = $development['appid'];
            }
        }

        //检查令牌是否存在
        if (empty($appid)) {
            throw_exception(10100);
        }

        //获取应用信息
        $appInfo = m('app')->getRow(['appid' => $appid]);

        //校验应用数据
        if (empty($appInfo) || $appInfo['is_enabled'] == 0) {
            throw_exception(10101);
        }

        //校验请求方式
        if ($requestType !== $appInfo['request_type']) {
            throw_exception(10002);
        }

        //校验签名盐
        if (empty($appInfo['secret'])) {
            throw_exception(10103);
        }

        //校验应用入口类型
        if (intval($appInfo[$api]) != 1) {
            throw_exception(10101);
        }

        //接口调用有效期

        //校验签名
        $dev = config('development');
        if (!($dev['debug'] == true && $dev['check_sign'] == false)) {
            if ($this->checkSign(input(), $appInfo) != true) {
                throw_exception(10104);
            }
        }

        $this->appInfo = $appInfo;
    }

    /**
     * 校验签名
     * @param $data
     * @param $app
     * @return mixed
     */
    protected function checkSign($data, $app)
    {
        return u('Sign', true)->checkSign($data, $app['secret']);
    }

    /**
     * 校验登录白名单
     * @return bool
     */
    protected function checkLoginWhitelist()
    {
        //校验登录白名单
        $loginWhitelist = config('login_whitelist', []);

        $result = false;
        foreach ($loginWhitelist as &$value) {
            if (!empty($value)) {
                $value = strtolower($value);
                $arr = explode('.', $value);
                //添加*通配符
                if ($arr[2] === '*' && $arr[1] === $this->controller) {
                    $result = true;
                    break;
                }
            }
        }
        unset($value);

        if ($result === false
            && !empty($this->method)
            && !empty($loginWhitelist)
            && in_array(strtolower($this->method), $loginWhitelist)) {
            $result = true;
        }

        return $result;
    }

    /**
     * 获取枚举数据
     * @return \think\response\Json|void
     * @throws \ReflectionException
     */
    protected function getModelEnumeration() {
        $data = input('data', '');

        if (empty($data)) {
            return show_json(10004);
        }

        $result = [];
        foreach ($data as $key => $value) {
            if (class_exists(app_namespace('model', $key)) == true) {
                $enumeration = m($key)->enumeration;
                if(!empty($enumeration)) {
                    foreach ($value as $val) {
                        $result[$key][$val] = isset($enumeration[$val]) ? $enumeration[$val] : null;
                    }
                }
            }
        }

        return show_json(0, '操作成功', $result);
    }

}
