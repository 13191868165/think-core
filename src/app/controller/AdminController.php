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
    }

    /**
     * 校验用户访问token，成功则返回用户信息
     */
    protected function checkToken($token)
    {
        if (empty($token)) {
            throw_exception(10209);
        }

        $payload = u('Jwt')::decode($token);
        if ($payload === false || empty($payload->id) || empty($payload->login_time) || empty($payload->entry)) {
            throw_exception(10211);
        }

        //校验令牌过期时间
        if (($payload->login_time + config("{$this->api}.login_time")) < time()) {
            throw_exception(10210);
        }

        //获取用户数据
        $user = m('admin')->getUser($payload->id);

        //检查用户是否存在
        if (empty($user)) {
            throw_exception(10204);
        }

        //是否在其它设备登录
        /*if (!empty($user['login_time']) && $user['login_time'] != $payload->login_time) {
            throw_exception(10207);
        }*/

        //是否禁用
        if ($user['is_enabled'] != 1) {
            throw_exception(10208);
        }

        return [
            //管理员会员信息
            'id' => $user['id'],
            'group_id' => $user['group_id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'realname' => $user['realname'],
            'avatar' => $user['avatar'],
            'phone' => $user['phone'],
            'sex' => $user['sex'],
            'user_id' => $user['user_id'],
            'merchant_id' => $user['merchant_id'],
            'is_merchant_admin' => $user['is_merchant_admin'],
            'status' => $user['status'],
            'create_time' => $user['create_time'],
            'create_time_date' => $user['create_time_date'],
            'login_time' => $user['login_time'],
            'login_time_date' => $user['login_time_date'],
            //用户组信息
            'title' => $user['title'],
            'admin_type' => $user['admin_type'],
            'admin_rules' => $user['admin_rules'],
            //商户信息
            'merchant_name' => $user['merchant_name'],
            'merchant_logo' => $user['merchant_logo'],
            'merchant_banner' => $user['merchant_banner'],
        ];
    }

}
