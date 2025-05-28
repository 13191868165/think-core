<?php
declare (strict_types=1);
namespace app\controller;

use think\App;

/**
 * Admin基类
 * Class AdminController
 * @package app
 */
abstract class ApiController extends BaseController
{
    /**
     * 来源
     * @var
     */
    protected $source;

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
        if (isset($data['method'])) {
            $method = explode('.', $data['method']);

            $fileName = count($method) == 2 ? strtolower($method[0]) : strtolower($method[1]);
            $action = count($method) == 2 ? strtolower($method[1]) : strtolower($method[2]);
            //不是上传文件 记录到日志中
        }
    }

    /**
     * 校验用户访问token，成功则返回用户信息
     */
    public function checkSetUser($token)
    {
        if (empty($token)) {
            throw_exception(10209);
        }

        $payload = f('Jwt')::decode($token);
        if ($payload === false || empty($payload->id) || empty($payload->login_time)) {
            throw_exception(10211);
        }

        //校验令牌过期时间
        if (($payload->login_time + config("{$this->api}.login_time")) < time()) {
            throw_exception(10210);
        }

        //获取用户数据
        $user = m('user')->getUser($payload->id);

        //检查用户是否存在
        if (empty($user)) {
            throw_exception(10206);
        }

        //是否在其它设备登录
        /*if (!empty($user['login_time']) && $user['login_time'] != $payload->login_time) {
            throw_exception(10207);
        }*/

        //是否禁用
        if ($user['is_enabled'] != 1) {
            throw_exception(10208);
        }

        $this->user = [
            'id' => $user['id'],
            'level_id' => $user['level_id'],
            'category_id' => $user['category_id'],
            'username' => $user['username'],
            'phone' => $user['phone'],
            'email' => $user['email'],
            'nickname' => $user['nickname'],
            'realname' => $user['realname'],
            'gender' => $user['gender'],
            'status' => $user['status'],
            'create_time' => $user['create_time'],
            'create_time_date' => $user['create_time_date'],
            'login_time' => $user['login_time'],
            'login_time_date' => $user['login_time_date'],
        ];
    }

}
