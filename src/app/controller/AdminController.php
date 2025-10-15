<?php
declare (strict_types=1);
namespace app\controller;

use think\App;

/**
 * Admin基类
 * Class AdminController
 * @package app
 */
abstract class AdminController extends BaseController
{
    /**
     * 模型名称
     * @var string
     */
    protected static $model = '';

    //初始化
    protected function initialize()
    {
        $this->_initialize();

        $data = input();

        $fileName = $this->api;
        $action = $this->action;
        //不是上传文件 记录到日志中
        if (!in_array($fileName, ['common', 'upload'])) {
            $params = [];
            $user = $this->user;
            if ($user) {
                $params['info'] = "操作人信息:{$user['username']},ID:{$user['id']}";
            }
            if ($fileName == 'admin' && $action == 'login') {
                $params = ['username' => isset($data['username']) ? trim($data['username']) : '', 'IP' => $_SERVER['REMOTE_ADDR']];
            } else {
                $params['data'] = json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            $filePath = $fileName . 'Log/' . date('Ymd') . '/' . $this->controller . '_' . $action . '.log';
            outlog($params, $filePath . '|1|0|1');
        }
    }

    /**
     * 检查token并设置用户信息
     * @param $token
     * @return array
     * @throws \ReflectionException
     */
    protected function checkSetUser($token)
    {
        if (empty($token)) {
            throw_exception(10209);
        }

        $payload = f('Jwt')::decode($token);
        if ($payload === false || empty($payload->id) || empty($payload->login_time) || empty($payload->admin_type)) {
            throw_exception(10211);
        }

        // 校验令牌过期时间
        if (($payload->login_time + config("{$this->api}.login_time")) < time()) {
            throw_exception(10210);
        }

        // 获取用户数据
        $user = m('admin')->getUser($payload->id);

        // 检查用户是否存在
        if (empty($user)) {
            throw_exception(10206);
        }

        // 校验用户登录后静默时间
        $stopTime = config("{$this->api}.stop_time");
        $time = time();
        $diffTime = $time - $user['login_time'];
        if (!config('development.debug')) {
            if (($stopTime - $diffTime) <= 0) {
                $stopTime2 = intval($stopTime / 60);
                throw_exception(10101, "用户{$stopTime2}分钟内无操作，系统自动退出");
            } else if ($diffTime > 180) {
                m('admin')->where(['id' => $user['id']])->save(['login_time' => $time]);
            }
        }

        //设置管理员类型
        $this->adminType = $user['admin_type'];

        // 是否在其它设备登录
        /*if (!empty($user['login_time']) && $user['login_time'] != $payload->login_time) {
            throw_exception(10207);
        }*/

        if ($this->adminType !== 1) {
            // 审核状态
            if ($user['status'] === 0) {
                throw_exception(10212);
            } elseif ($user['status'] == 1) {
                throw_exception(10213);
            }

            // 是否禁用
            if ($user['is_enabled'] != 1) {
                throw_exception(10208);
            }
        }

        $this->user = [
            // 管理员会员信息
            'id' => $user['id'],
            'gid' => $user['gid'],
            'username' => $user['username'],
            'email' => $user['email'],
            'realname' => $user['realname'],
            'avatar' => $user['avatar'],
            'phone' => $user['phone'],
            'gender' => $user['gender'],
            'admin_role_id' => $user['admin_role_id'],
            'status' => $user['status'],
            'create_time' => $user['create_time'],
            'create_time_date' => $user['create_time_date'],
            'login_time' => $user['login_time'],
            'login_time_date' => $user['login_time_date'],
            // 用户组信息
            'title' => $user['title'],
            'admin_type' => $user['admin_type'],
            'admin_rules' => $user['admin_rules'],

            'province_id' => $user['province_id'],
            'city_id' => $user['city_id'],
            'area_id' => $user['area_id'],

            'is_reset' => $user['is_reset'], //是否强制重置密码
        ];
    }

    /**
     * 检查管理员权限
     * @return void
     * @throws \ReflectionException
     */
    protected function checkRouteRule()
    {
        $routeName = $this->request->header('x-route-name', 'undefined');
        if (empty($routeName) || $routeName == 'undefined') {
            $routeName = 'undefined';
        }

        if (env('APP_DEBUG', false) && config("development.debug", [])) {
            $routeData[$routeName] = $this->method;
            //开发模式写入管理员权限
            m('routes')->writePermission($routeData);
            //开发模式删除缓存文件
            m('routes')->adminPermissionCache('delete', [], $this->user['admin_type']);
        }

        $checkRouteByUser = m('routes')->setUser($this->user)->checkRouteByUser($this->method);
        if (!$checkRouteByUser) {
            throw_exception(10214);
        }
    }
}
