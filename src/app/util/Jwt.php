<?php
namespace app\util;

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
        // 使用Firebase JWT解码并返回
        return \Firebase\JWT\JWT::encode($payload, self::$config['secret'], self::$config['algo']);
    }

    /**
     * JWT解密
     * @param $jwt
     * @return bool|object
     */
    public static function decode($jwt)
    {
        // 使用Firebase JWT解码
        try {
            $key = new \Firebase\JWT\Key(self::$config['secret'], self::$config['algo']);
            $decode = \Firebase\JWT\JWT::decode($jwt, $key);
            return $decode;
        } catch (\think\Exception $e) {
            return false;
        }
    }

}
