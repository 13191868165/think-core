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

        $this->user = [
            'id' => $user['id'],
            'level_id' => $user['level_id'],
            'category_id' => $user['category_id'],
            'username' => $user['username'],
            'phone' => $user['phone'],
            'email' => $user['email'],
            'credit1' => $user['credit1'],
            'credit2' => $user['credit2'],
            'nickname' => $user['nickname'],
            'realname' => $user['realname'],
            'avatar' => $user['avatar'],
            'sex' => $user['sex'],
            'sex_data' => $user['sex_data'],
            'id_card' => $user['id_card'],
            'vip' => $user['vip'],
            'vip_data' => $user['vip_data'],
            'tags' => $user['tags'],
            'birthday' => $user['birthday'],
            'birthday_date' => $user['birthday_date'],
            'province' => $user['province'],
            'city' => $user['city'],
            'area' => $user['area'],
            'address' => $user['address'],
            'lat' => $user['lat'],
            'lng' => $user['lng'],
        ];
    }

}
