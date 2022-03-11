<?php
declare (strict_types=1);

namespace app\middleware;

/**
 * 跨域
 * Class AccessCors
 * @package app\middleware
 */
class AccessCors
{
    public function handle($request, \Closure $next)
    {
        //打开跨域
        header('Content-Type: *');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');//设置允许访问的协议
        header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
        header('Access-Control-Allow-Headers: *');
        header('Content-Type:text/html; charset=utf-8');//响应类型

        return $next($request);
    }
}
