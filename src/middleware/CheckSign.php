<?php
declare (strict_types=1);

namespace app\core\middleware;


/**
 * 签名验证
 * Class CheckSign
 * @package app\middleware
 */
class CheckSign
{
    public function handle($request, \Closure $next)
    {

        $response = $next($request);

        //校验路由是否合法
        $controller = explode('.', $request->controller());
        if (count($controller) != 2) {
            throw new \app\MyException(10002);
        }

        $site = $controller[0];

        //开发调试模式
        $dev = get_config("{$site}.development");
        $devAppid = $dev['mode'] == true ? $dev['appid'] : '';

        //获取令牌
        $appid = $request->header('x-access-appid', $devAppid);

        //检查令牌是否存在
        if (empty($appid)) {
            throw new \app\MyException(10100);
        }

        //校验令牌是否合法
        $app = m('app')->getRow(['appid' => $appid]);
        if (empty($app) || $app['is_enabled'] == 0) {
            throw new \app\MyException(10101);
        }

        if (empty($app['secret'])) {
            throw new \app\MyException(10103);
        }

        if (intval($app[$site]) != 1) {
            throw new \app\MyException(10101);
        }
        if ($site != 'admin' && $site != 'api') {
            throw new \app\MyException(10102);
        }

        //登录白名单
        $config = get_config($site);
        if ($request->pathinfo() && $config['sign_white_list'] && in_array($request->pathinfo(), $config['sign_white_list'])) {
            return $response;
        }

        //签名认证
        $param = $request->param();
        unset($param['action']);
        unset($param['controller']);

        //开发调试模式
        $devSign = $dev['mode'] == true ? get_sign($param, $app['secret']) : '';
        $sign = $request->param('sign', $devSign);
        if (empty($sign)) {
            throw new \app\MyException(10104);
        }
        unset($param['sign']);

        if (get_sign($param, $app['secret']) != $sign) {
            throw new \app\MyException(10104);
        }

        return $response;
    }
}