<?php
// +----------------------------------------------------------------------
// | 错误码定义
// +----------------------------------------------------------------------

return [
    //系统相关错误
    10000 => '系统错误',
    10001 => '配置错误',
    10002 => '非法请求',

    //验签相关错误
    10100 => '应用令牌缺失',
    10101 => '应用令牌失效',
    10102 => '应用令牌无访问权限',
    10103 => '应用签名盐失效',
    10104 => '签名错误',

    //用户登录相关错误
    10200 => '用户名不能为空',
    10201 => '密码不能为空',
    10202 => '验证码不能为空',
    10203 => '验证码错误',
    10204 => '用户不存在',
    10205 => '用户密码错误',
    10206 => '用户名密码不匹配',
    10207 => '用户在其它设备登录',
    10208 => '用户已被禁用',
    10209 => '用户访问令牌缺失',
    10210 => '用户访问令牌过期',
    10211 => '用户访问令牌失效',
];