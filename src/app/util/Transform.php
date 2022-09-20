<?php
namespace app\util;

/**
 * 数据转换
 * Class Transform
 * @package app\db
 */
class Transform
{

    /**
     * 转字符串
     * @param $value
     * @return string
     */
    public function string($value)
    {
        return (string)$value;
    }

    /**
     * 转int
     * @param $value
     * @return int
     */
    public function integer($value)
    {
        return (int)$value;
    }

    /**
     * internal别名
     * @param $value
     * @return int
     */
    public function int($value)
    {
        return $this->integer($value);
    }

    /**
     * 转float
     * @param $value
     * @param null $decimals
     * @return float
     */
    public function float($value, $decimals = null)
    {
        return isset($decimals) ? (float)number_format($value, (int)$decimals, '.', '') : (float)$value;
    }

    /**
     * 转bool
     * @param $value
     * @return bool
     */
    public function boolean($value)
    {
        return (bool)$value;
    }


    /**
     * 值不为 null
     * @param $value
     * @param string $default
     * @return string
     */
    public function isset($value, $default = '')
    {
        return isset($value) ? $value : $default;
    }

    /**
     * 值非空、非0、非0.0、非 null、非false、非 array()、非空变量
     * @param $value
     * @param string $default
     * @return string
     */
    public function empty($value, $default = '')
    {
        return !empty($value) ? $value : $default;
    }

    /**
     * 值非 null、非空
     * @param $value
     * @param string $default
     * @return string
     */
    public function isEmpty($value, $default = '')
    {
        return !isset($value) || $value === '' ? $default : $value;
    }

    /**
     * 变量整数值
     * @param $value
     * @param int $default
     * @return int
     */
    public function intval($value, $default = 0)
    {
        return !isset($value) ? intval($value) : $default;
    }


    /**
     * 移除字符串两侧的字符
     * @param $value
     * @param bool $charlist
     * @return string
     */
    public function trim($value, $charlist = false)
    {
        return $charlist === false ? trim($value) : trim($value, $charlist);
    }

    /**
     * 移除字符串左侧的字符
     * @param $value
     * @param bool $charlist
     * @return string
     */
    public function ltrim($value, $charlist = false)
    {
        return $charlist === false ? ltrim($value) : ltrim($value, $charlist);
    }

    /**
     * 移除字符串右侧的字符
     * @param $value
     * @param bool $charlist
     * @return string
     */
    public function rtrim($value, $charlist = false)
    {
        return $charlist === false ? rtrim($value) : rtrim($value, $charlist);
    }

    /**
     * 时间戳转日期
     * @param $value
     * @param string $type
     * @param string $default
     * @return mixed
     */
    public function datetime($value, $type = 'Y-m-d H:i:s', $default = null)
    {
        if (!empty($value)) {
            $value = is_numeric($value) ? $value : strtotime($value);
            $value = date($type, $value);
        } else {
            $value = $default === null ? $value : $default;
        }
        return $value;
    }

    /**
     * 日期字符串转时间戳
     * @param $value
     * @param null $default
     * @return false|int|null
     */
    public function timestamp($value, $default = null)
    {
        return !empty($value) ? strtotime($value) : $default;
    }

    /**
     * 数组转字符串
     * @param $value
     * @param string $separator
     * @return string
     */
    public function implode($value, $separator = '')
    {
        return implode($separator, $value);
    }

    /**
     * 字符串打散为数组
     * @param $value
     * @param string $separator
     * @return array
     */
    public function explode($value, $separator = ',')
    {
        return explode($separator, $value);
    }

    /**
     * json编码
     * @param $value
     * @param int $options
     * @param int $depth
     * @return false|string
     */
    public function jsonEncode($value, $options = JSON_UNESCAPED_UNICODE, $depth = 512)
    {
        return json_encode($value, $options, $depth);
    }

    /**
     * json解码
     * @param $value
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    public function jsonDecode($value, $assoc = true, $depth = 512, $options = 0)
    {
        return json_decode($value, $assoc, $depth, $options);
    }

    /**
     * base64编码
     * @param $value
     * @return string
     */
    public function base64Encode($value)
    {
        return base64_encode($value);
    }

    /**
     * base64解码
     * @param $value
     * @return false|string
     */
    public function base64Decode($value)
    {
        return base64_decode($value);
    }

    /**
     * 保存html
     * @param $value
     * @return string
     */
    public function saveHtml($value)
    {
        return htmlspecialchars($value);
    }

    /**
     * 读取html
     * @param $value
     * @return string
     */
    public function readHtml($value)
    {
        return html_entity_decode($value);
    }

    /**
     * 序列化
     * @param $value
     * @return string
     */
    public function serialize($value)
    {
        return serialize($value);
    }

    /**
     * 反序列化
     * @param $value
     * @return mixed
     */
    public function unserialize($value)
    {
        return unserialize($value);
    }


    /**
     * hash加密
     * @param $value
     * @param string $salt
     * @return mixed
     */
    public function hash($value, $salt = '')
    {
        return f('Str')::createHash($value, $salt);
    }

    /**
     * 价格格式化
     * @param $value
     * @param int $len
     * @param string $separator
     * @param bool $abs
     * @return mixed
     */
    public function priceFormat($value, $len = 2, $separator = '', $abs = true)
    {
        return f('Str')::priceFormat($value, $len, $separator, $abs);
    }

    /**
     * 数据脱敏
     * @param $value
     * @param int $start
     * @param int $len
     * @param string $separator
     * @param int $sepLen 符号长度
     * @return mixed
     */
    public function dataMasking($value, $start = 0, $len = 0, $separator = '*', $sepLen = 0)
    {
        return f('Str')::dataMasking($value, $start, $len, $separator, $sepLen);
    }

    /**
     * 生成随机数
     * @param string $value
     * @param int $length
     * @param null $type
     * @param string $addChars
     * @return mixed
     */
    public function random($value = '', $length = 6, $type = null, $addChars = '')
    {
        return f('Str')::random($length, $type, $addChars);
    }

    /**
     * 获取ip
     * @param string $value
     * @return mixed
     */
    public function getip($value = '')
    {
        return f('Str')::getip();
    }

}
