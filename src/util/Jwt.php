<?php
namespace app\core\model;

class Jwt
{

    public static $config = [
        'secret' => 'aa5068b8f93ab9b0161b880152fcff03',
        'algo' => 'HS256',
    ];

    /**
     * JWT加密
     * @param $payload
     * @return string
     */
    public static function encode($payload)
    {
        $secretKey = self::$config['secret'];
        $algorithm = self::$config['algo'];
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
        $secretKey = self::$config['secret'];
        $algorithm = self::$config['algo'];
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
