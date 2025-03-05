<?php
namespace app\util;
class Validate
{
    /**
     * 是否是字符串
     * @param $value
     * @return bool
     */
    public function isString($value)
    {
        return is_string($value);
    }

    /**
     * 是否为数字
     * @param $value
     * @return false|int
     */
    public function isNumber($value)
    {
        return preg_match("/^[0-9]*$/", $value);
    }

    /**
     * 数字/货币金额 (只支持正数、不支持校验千分位分隔符)
     * @param $value
     * @return false|int
     */
    public function isPrice($value)
    {
        return preg_match("/(?:^[1-9]([0-9]+)?(?:\.[0-9]{1,2})?$)|(?:^(?:0)$)|(?:^[0-9]\.[0-9](?:[0-9])?$)/", $value);
    }

    /**
     * 是否是名称，字符串+数字
     * @param $value
     * @return false|int
     */
    public function isName($value)
    {
        return preg_match("/^[\u4e00-\u9fa5a-zA-Z0-9]+$/", $value);
    }

    /**
     * 是否是手机号
     * @param $value
     * @return false|int
     */
    public function isPhone($value)
    {
        return preg_match("/^(?:(?:\+|00)86)?1\d{10}$/", $value);
    }

    /**
     * 香港身份证
     * @param $value
     * @return false|int
     */
    public function isHKCard($value)
    {
        return preg_match("/^[1|5|7]\d{6}\(\d\)$/", $value);
    }

    /**
     * 澳门身份证
     * @param $value
     * @return false|int
     */
    public function isAMCard($value)
    {
        return preg_match("/^[a-zA-Z]\d{6}\([\dA]\)$/", $value);
    }

    /**
     * 台湾身份证
     * @param $value
     * @return false|int
     */
    public function isTWCard($value)
    {
        return preg_match("/^[a-zA-Z][0-9]{9}$/", $value);
    }

    /**
     * 判断是否是身份证号
     * @param $value
     * @param $type
     * @return false|int
     */
    public function isIdCard($value, $type = '')
    {
        if (!empty($type)) {
            if ($type == 1) { //支持1/2代(15位/18位数字)
                return preg_match("/^\d{6}((((((19|20)\d{2})(0[13-9]|1[012])(0[1-9]|[12]\d|30))|(((19|20)\d{2})(0[13578]|1[02])31)|((19|20)\d{2})02(0[1-9]|1\d|2[0-8])|((((19|20)([13579][26]|[2468][048]|0[48]))|(2000))0229))\d{3})|((((\d{2})(0[13-9]|1[012])(0[1-9]|[12]\d|30))|((\d{2})(0[13578]|1[02])31)|((\d{2})02(0[1-9]|1\d|2[0-8]))|(([13579][26]|[2468][048]|0[048])0229))\d{2}))(\d|X|x)$/", $value);
            } else { //支持2代,18位数字
                return preg_match("/^[1-9]\d{5}(?:18|19|20)\d{2}(?:0[1-9]|10|11|12)(?:0[1-9]|[1-2]\d|30|31)\d{3}[\dXx]$/", $value);
            }
        } else { //15位和18位身份证号码的正则表达式
            return preg_match("/^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/", $value);
        }
    }

    /**
     * 护照（包含香港、澳门）
     * @param $value
     * @return false|int
     */
    public function isPassport($value)
    {
        return preg_match("/(^[EeKkGgDdSsPpHh]\d{8}$)|(^(([Ee][a-fA-F])|([DdSsPp][Ee])|([Kk][Jj])|([Mm][Aa])|(1[45]))\d{7}$)/", $value);
    }

    /**
     * 是否是邮箱
     * @param $value
     * @return false|int
     */
    public function isEmail($value)
    {
        return preg_match("/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/", $value);
    }

    /**
     * 是否是网址
     * @param $value
     * @return false|int
     */
    public function isUrl($value)
    {
        return preg_match("/^(https?|ftp):\/\/([a-zA-Z0-9.-]+(:[a-zA-Z0-9.&%$-]+)*@)*((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}|([a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]+\.(com|edu|gov|int|mil|net|org|biz|arpa|info|name|pro|aero|coop|museum|[a-zA-Z]{2}))(:[0-9]+)*(\/($|[a-zA-Z0-9.,?'\\+&%$#=~_-]+))*$/", $value);
    }

    /**
     * 是否是外链
     * @param $value
     * @return false|int
     */
    public function isExternal($value)
    {
        return preg_match("/^(https?:|mailto:|tel:|\/\/)/", $value);
    }

    /**
     * 是否非中文
     * @param $value
     * @return false|int
     */
    public function noChinese($value)
    {
        return !$this->isChinese($value);
    }

    /**
     * 是否是中文
     * @param $value
     * @return false|int
     */
    public function isChinese($value)
    {
        return preg_match("/^[\u4E00-\u9FA5]{2,4}$/", $value);
    }

    /**
     *
     * @param $value
     * @return false|int
     */
    public function isEnglish($value)
    {
        return preg_match("/^[a-zA-Z]+$/", $value);
    }

    /**
     * 是否是视频链接
     * @param $value
     * @return false|int
     */
    public function isVideo($value)
    {
        return preg_match("/^https?:\/\/(.+\/)+.+(\.(swf|avi|flv|mpg|rm|mov|wav|asf|3gp|mkv|rmvb|mp4))$/i", $value);
    }

    /**
     * 是否是base64
     * @param $value
     * @return false|int
     */
    public function isBase64($value)
    {
        return preg_match("/^\s*data:(?:[a-z]+\/[a-z0-9-+.]+(?:;[a-z-]+=[a-z0-9-]+)?)?(?:;base64)?,([a-z0-9!$&',()*+;=\-._~:@/?%\s]*?)\s*$/i", $value);
    }

    /**
     * 密码是否小于6位
     * @param $value
     * @return bool
     */
    public function isPassword($value)
    {
        return strlen($value) >= 6;
    }

    /**
     *
     * @param $value
     * @return false|int
     */
    public function isIP($value)
    {
        //ipv4
        if (!preg_match("/^((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])(?::(?:[0-9]|[1-9][0-9]{1,3}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5]))?$/", $value)) {
            //ipv6
            return preg_match("/(^(?:(?:(?:[0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$)|(^\[(?:(?:(?:[0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))\](?::(?:[0-9]|[1-9][0-9]{1,3}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5]))?$)/i", $value);
        }
        return true;
    }

    /**
     * 是否是小写字母
     * @param $value
     * @return false|int
     */
    public function isLowerCase($value)
    {
        return preg_match("/^[a-z]+$/", $value);
    }

    /**
     * 是否是大写字母
     * @param $value
     * @return false|int
     */
    public function isUpperCase($value)
    {
        return preg_match("/^[A-Z]+$/", $value);
    }

    /**
     * 是否是大写字母开头
     * @param $value
     * @return false|int
     */
    public function isAlphabets($value)
    {
        return preg_match("/^[A-Za-z]+$/", $value);
    }

    /**
     * 是否是端口号
     * @param $value
     * @return false|int
     */
    public function isPort($value)
    {
        return preg_match("/^([0-9]|[1-9]\d|[1-9]\d{2}|[1-9]\d{3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/", $value);
    }

    /**
     * 是否为固话
     * @param $value
     * @return false|int
     */
    public function isTel($value)
    {
        return preg_match("/^(400|800)([0-9\\-]{7,10})|(([0-9]{4}|[0-9]{3})([- ])?)?([0-9]{7,8})(([- 转])*([0-9]{1,4}))?$/", $value);
    }

    /**
     * 是否为数字且最多两位小数
     * @param $value
     * @return false|int
     */
    public function isNum($value)
    {
        return preg_match("/^\d+(\.\d{1,2})?$/", $value);
    }

    /**
     * 是否为json
     * @param $value
     * @return bool
     */
    public function isJson($value)
    {
        return is_null(json_decode($value));
    }

    /**
     * 是否是微信
     * @param $value
     * @return bool
     */
    public function isWeixin($value)
    {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }
}
