<?php
namespace core\model;

class Jwt
{

    /**
     * JWT加密
     * @param $payload
     * @return string
     */
    public static function encode($payload)
    {
        $secretKey = config('jwt.secret');
        $algorithm = config('jwt.algo');
        if(!$secretKey || !$algorithm) {
            throw_exception(10001, 'JWT配置错误');
        }

        // 使用Firebase JWT解码并返回
        return \Firebase\JWT\JWT::encode($payload, $secretKey, $algorithm);
    }

    /**
     * JWT解密
     * @param $jwt
     * @return bool|object
     */
    public static function decode($jwt)
    {
        $secretKey = config('jwt.secret');
        $algorithm = config('jwt.algo');
        if(!$secretKey || !$algorithm) {
            throw_exception(10001, 'JWT配置错误');
        }

        // 使用Firebase JWT解码
        try {
            $decode = \Firebase\JWT\JWT::decode($jwt, $secretKey, array($algorithm));
            return $decode;
        }catch(\think\Exception $e) {
            return false;
        }
    }

}
