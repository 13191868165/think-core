<?php
declare (strict_types=1);
namespace app\controller;

/**
 * 管理端基类
 * Class AdminController
 * @package app
 */
abstract class AdminController extends BaseController
{

    //模型名称
    protected static $model = '';
    //用户信息
    protected $user = [];

    //初始化
    protected function initialize()
    {
        //初始化模型名称
        $name       = str_replace('\\', '/', static::class);
        $this::$model = basename($name);

        //检查登录白名单
        if ($this->checkLoginWhiteList()) {
            return true;
        }

        //开发调试模式
        $dev = core_config("development.{$this->site}");
        $devToken = $dev['debug'] == true ? $dev['token'] : '';

        //校验用户访问令牌
        $user = m('admin')->checkToken($this->request->header('x-access-token', $devToken));
        //设置用户信息
        $this->user = $user;
        //设置用户权限
        //$this->user['group']
        //
    }

    /**
     * 检查登录白名单
     * @param string $site
     * @return bool
     */
    private function checkLoginWhiteList($site = '')
    {
        if (empty($site)) {
            $site = $this->site;
        }

        $path = request()->pathinfo();

        $white_list = core_config("{$site}.login_white_list");
        if (!empty($path) && !empty($white_list) && in_array($path, $white_list)) {
            return true;
        }

        return false;
    }

}
