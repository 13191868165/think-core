<?php
namespace app\util\account;

use think\facade\Cache;

/**
 * 微信
 */
class Wechat extends Account
{

    protected $config = array();
    protected $access_token = '';

    public function __construct($config = array())
    {
        $this->config = array(
            'appid' => $config['appid'],
            'secret' => $config['secret'],
            'token' => $config['token'],
            'EncodingAESKey' => $config['EncodingAESKey'],
        );
    }

    public function errorCode($code, $errmsg = '未知错误')
    {
        $errors = array(
            '-1' => '系统繁忙',
            '0' => '请求成功',
            '40001' => '获取access_token时AppSecret错误，或者access_token无效',
            '40002' => '不合法的凭证类型',
            '40003' => '不合法的OpenID',
            '40004' => '不合法的媒体文件类型',
            '40005' => '不合法的文件类型',
            '40006' => '不合法的文件大小',
            '40007' => '不合法的媒体文件id',
            '40008' => '不合法的消息类型',
            '40009' => '不合法的图片文件大小',
            '40010' => '不合法的语音文件大小',
            '40011' => '不合法的视频文件大小',
            '40012' => '不合法的缩略图文件大小',
            '40013' => '不合法的APPID',
            '40014' => '不合法的access_token',
            '40015' => '不合法的菜单类型',
            '40016' => '不合法的按钮个数',
            '40017' => '不合法的按钮个数',
            '40018' => '不合法的按钮名字长度',
            '40019' => '不合法的按钮KEY长度',
            '40020' => '不合法的按钮URL长度',
            '40021' => '不合法的菜单版本号',
            '40022' => '不合法的子菜单级数',
            '40023' => '不合法的子菜单按钮个数',
            '40024' => '不合法的子菜单按钮类型',
            '40025' => '不合法的子菜单按钮名字长度',
            '40026' => '不合法的子菜单按钮KEY长度',
            '40027' => '不合法的子菜单按钮URL长度',
            '40028' => '不合法的自定义菜单使用用户',
            '40029' => '不合法的oauth_code',
            '40030' => '不合法的refresh_token',
            '40031' => '不合法的openid列表',
            '40032' => '不合法的openid列表长度',
            '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
            '40035' => '不合法的参数',
            '40038' => '不合法的请求格式',
            '40039' => '不合法的URL长度',
            '40048' => '无效的url',
            '40050' => '不合法的分组id',
            '40051' => '分组名字不合法',
            '40060' => '删除单篇图文时，指定的 article_idx 不合法',
            '40117' => '分组名字不合法',
            '40118' => 'media_id 大小不合法',
            '40119' => 'button 类型错误',
            '40120' => '子 button 类型错误',
            '40121' => '不合法的 media_id 类型',
            '40125' => '无效的appsecret',
            '40132' => '微信号不合法',
            '40137' => '不支持的图片格式',
            '40155' => '请勿添加其他公众号的主页链接',
            '40163' => 'oauth_code已使用',
            '41001' => '缺少access_token参数',
            '41002' => '缺少appid参数',
            '41003' => '缺少refresh_token参数',
            '41004' => '缺少secret参数',
            '41005' => '缺少多媒体文件数据',
            '41006' => '缺少media_id参数',
            '41007' => '缺少子菜单数据',
            '41008' => '缺少oauth code',
            '41009' => '缺少openid',
            '42001' => 'access_token超时',
            '42002' => 'refresh_token超时',
            '42003' => 'oauth_code超时',
            '42007' => '用户修改微信密码， accesstoken 和 refreshtoken 失效，需要重新授权',
            '42010' => '相同 media_id 群发过快，请重试',
            '43001' => '需要GET请求',
            '43002' => '需要POST请求',
            '43003' => '需要HTTPS请求',
            '43004' => '需要接收者关注',
            '43005' => '需要好友关系',
            '43019' => '需要将接收者从黑名单中移除',
            '44001' => '多媒体文件为空',
            '44002' => 'POST的数据包为空',
            '44003' => '图文消息内容为空',
            '44004' => '文本消息内容为空',
            '45001' => '多媒体文件大小超过限制',
            '45002' => '消息内容超过限制',
            '45003' => '标题字段超过限制',
            '45004' => '描述字段超过限制',
            '45005' => '链接字段超过限制',
            '45006' => '图片链接字段超过限制',
            '45007' => '语音播放时间超过限制',
            '45008' => '图文消息超过限制',
            '45009' => '接口调用超过限制',
            '45010' => '创建菜单个数超过限制',
            '45011' => 'API 调用太频繁，请稍候再试',
            '45015' => '回复时间超过限制',
            '45016' => '系统分组，不允许修改',
            '45017' => '分组名字过长',
            '45018' => '分组数量超过上限',
            '45047' => '客服接口下行条数超过上限',
            '45065' => '24小时内不可给该组人群发该素材',
            '45066' => '相同 clientmsgid 重试速度过快，请间隔1分钟重试',
            '45067' => 'clientmsgid 长度超过限制',
            '46001' => '不存在媒体数据',
            '46002' => '不存在的菜单版本',
            '46003' => '不存在的菜单数据',
            '46004' => '不存在的用户',
            '47001' => '解析JSON/XML内容错误',
            '47003' => '参数值不符合限制要求，详情可参考参数值内容限制说明',
            '48001' => 'api功能未授权',
            '48002' => '粉丝拒收消息（粉丝在公众号选项中，关闭了 “ 接收消息 ” ）',
            '48004' => 'api 接口被封禁，请登录 mp.weixin.qq.com 查看详情',
            '48005' => 'api 禁止删除被自动回复和自定义菜单引用的素材',
            '48006' => 'api 禁止清零调用次数，因为清零次数达到上限',
            '48008' => '没有该类型消息的发送权限',
            '50001' => '用户未授权该api',
            '50002' => '用户受限，可能是违规后接口被封禁',
            '50005' => '用户未关注公众号',
            '53500' => '发布功能被封禁',
            '53501' => '频繁请求发布',
            '53502' => 'Publish ID 无效',
            '53600' => 'Article ID 无效',
            '61451' => '参数错误 (invalid parameter)',
            '61452' => '无效客服账号 (invalid kf_account)',
            '61453' => '客服帐号已存在 (kf_account exsited)',
            '61454' => '客服帐号名长度超过限制 ( 仅允许 10 个英文字符，不包括 @ 及 @ 后的公众号的微信号 )(invalid   kf_acount length)',
            '61455' => '客服帐号名包含非法字符 ( 仅允许英文 + 数字 )(illegal character in     kf_account)',
            '61456' => '客服帐号个数超过限制 (10 个客服账号 )(kf_account count exceeded)',
            '61457' => '无效头像文件类型 (invalid   file type)',
            '61450' => '系统错误 (system error)',
            '61500' => '日期格式错误',
            '63001' => '部分参数为空',
            '63002' => '无效的签名',
            '65301' => '不存在此 menuid 对应的个性化菜单',
            '65302' => '没有相应的用户',
            '65303' => '没有默认菜单，不能创建个性化菜单',
            '65304' => 'MatchRule 信息为空',
            '65305' => '个性化菜单数量受限',
            '65306' => '不支持个性化菜单的帐号',
            '65307' => '个性化菜单信息为空',
            '65308' => '包含没有响应类型的 button',
            '65309' => '个性化菜单开关处于关闭状态',
            '65310' => '填写了省份或城市信息，国家信息不能为空',
            '65311' => '填写了城市信息，省份信息不能为空',
            '65312' => '不合法的国家信息',
            '65313' => '不合法的省份信息',
            '65314' => '不合法的城市信息',
            '65316' => '该公众号的菜单设置了过多的域名外跳（最多跳转到 3 个域名的链接）',
            '65317' => '不合法的 URL',
            '87009' => '无效的签名',
        );
        $code = strval($code);
        if ($code == '40001' || $code == '42001') {
            return '微信公众平台授权异常.';
        }

        if ($code == '40164') {
            $pattern = "((([0-9]{1,3})(\.)){3}([0-9]{1,3}))";
            preg_match($pattern, $errmsg, $out);

            $ip = !empty($out) ? $out[0] : '';
            return '获取授权失败，错误代码:' . $code . ' 错误信息: ip-' . $ip . '不在白名单之内！';
        }

        return empty($errors[$code]) ? "{$code}:{$errmsg}" : $errors[$code];
    }

    /**
     * 接入校验
     * @param $data
     * @return bool
     */
    public function checkSignature($data)
    {
        $echostr = '';
        if (empty($data)) {
            $signature = input('signature', '', 'trim');
            $timestamp = input('timestamp', '', 'trim');
            $nonce = input('nonce', '', 'trim');
            $echostr = input('echostr', '', 'trim');
        } else {
            $signature = $data['signature'];
            $timestamp = $data['timestamp'];
            $nonce = $data['nonce'];
            $echostr = $data['echostr'];
        }
        if (empty($signature) || empty($timestamp) || empty($nonce)) {
            return false;
        }

        $token = $this->config['token'];
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        return $tmpStr == $signature ? $echostr : '';
    }

    /**
     * https://developers.weixin.qq.com/doc/offiaccount/Basic_Information/Get_access_token.html
     * 获取Access token
     * @return array|mixed
     */
    public function getAccessToken()
    {
        $cachekey = "wechat:access_token";
        $cache = Cache::get($cachekey);
        if (!empty($cache) && !empty($cache['token']) && $cache['expire'] > time()) {
            $this->config['access_token'] = $cache;
            return $cache['token'];
        }
        if (empty($this->config['appid']) || empty($this->config['secret'])) {
            return ['code' => 1, 'msg' => '未填写公众号的 appid 或 appsecret！'];
        }
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->config['appid']}&secret={$this->config['secret']}";
        $result = $this->send($url);
        if (!empty($result['errcode'])) {
            return ['code' => 1, 'msg' => $this->errorCode($result['errcode'], $result['errmsg'])];
        }

        $record = array();
        $record['token'] = $result['access_token'];
        $record['expire'] = time() + $result['expires_in'] - 1000;
        $this->config['access_token'] = $record;
        Cache::set($cachekey, $record);
        return $record['token'];
    }

    /**
     * https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
     * 网页授权(只能获取用户openid)
     * @param $callback
     * @param string $state
     * @return string
     */
    public function getOauthCodeUrl($callback, $state = '')
    {
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->config['appid']}&redirect_uri={$callback}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
    }

    /**
     * 网页授权(弹出授权页面，获取更多信息)
     * @param $callback
     * @param string $state
     * @return string
     */
    public function getOauthUserInfoUrl($callback, $state = '')
    {
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->config['appid']}&redirect_uri={$callback}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
    }

    /**
     * https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/Wechat_webpage_authorization.html
     * code换取网页授权access_token
     * @param string $code
     * @return mixed
     */
    public function getOauthInfo($code = '')
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->config['appid']}&secret={$this->config['secret']}&code={$code}&grant_type=authorization_code";
        $result = $this->send($url);
        if (!empty($result['errcode'])) {
            return ['code' => 1, 'msg' => $this->errorCode($result['errcode'], $result['errmsg'])];
        }
        return $result;
    }

    public function getOauthUserInfo($accesstoken, $openid)
    {
        $apiurl = "https://api.weixin.qq.com/sns/userinfo?access_token={$accesstoken}&openid={$openid}&lang=zh_CN";
        $response = $this->requestApi($apiurl);
        unset($response['remark'], $response['subscribe_scene'], $response['qr_scene'], $response['qr_scene_str']);
        return $response;
    }

    protected function requestApi($url, $post = '')
    {
        $response = ihttp_request($url, $post);

        $result = @json_decode($response['content'], true);
        if (is_error($response)) {
            return error($result['errcode'], "访问公众平台接口失败, 错误详情: {$this->errorCode($result['errcode'])}");
        }
        if (empty($result)) {
            return error(-1, "接口调用失败, 元数据: {$response['meta']}");
        } elseif (!empty($result['errcode'])) {
            return error($result['errcode'], "访问公众平台接口失败, 错误: {$result['errmsg']},错误详情：{$this->errorCode($result['errcode'])}");
        }
        return $result;
    }

    public function getJsApiTicket()
    {
        $cachekey = "wechat:jsticket";
        $cache = Cache::get($cachekey);
        if (!empty($cache) && !empty($cache['ticket']) && $cache['expire'] > time()) {
            return $cache['ticket'];
        }
        $access_token = $this->getAccessToken();
        if (is_error($access_token)) {
            return $access_token;
        }
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
        $content = ihttp_get($url);
        if (is_error($content)) {
            return error(-1, '调用接口获取微信公众号 jsapi_ticket 失败, 错误信息: ' . $content['message']);
        }
        $result = @json_decode($content['content'], true);
        if (empty($result) || intval(($result['errcode'])) != 0 || $result['errmsg'] != 'ok') {
            return error(-1, '获取微信公众号 jsapi_ticket 结果错误, 错误信息: ' . $result['errmsg']);
        }
        $record = array();
        $record['ticket'] = $result['ticket'];
        $record['expire'] = time() + $result['expires_in'] - 200;
        $this->config['jsapi_ticket'] = $record;
        module_cache_write($cachekey, $record);
        return $record['ticket'];
    }

    public function getJssdkConfig($url = '')
    {
        $jsapiTicket = $this->getJsApiTicket();
        if (is_error($jsapiTicket)) {
            $jsapiTicket = $jsapiTicket['message'];
        }
        $nonceStr = random(16);
        $timestamp = time();
        $string1 = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string1);
        $config = array(
            "appId" => $this->config['appid'],
            "nonceStr" => $nonceStr,
            "timestamp" => "$timestamp",
            "signature" => $signature,
        );
        if (DEVELOPMENT) {
            $config['url'] = $url;
            $config['string1'] = $string1;
            $config['name'] = $this->config['name'];
        }
        return $config;
    }

}
