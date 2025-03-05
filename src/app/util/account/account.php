<?php
namespace app\util\account;

class Account
{

    /**
     * 网络请求
     * @param $url
     * @param $data
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request($url, $data = '')
    {
        $client = new \GuzzleHttp\Client(['verify' => false]);
        $options = [];
        if (!empty($data)) {
            $options['form_params'] = $data;
        }
        $response = $client->request(empty($data) ? 'GET' : 'POST', $url, $options);

        return (string)$response->getBody();
    }

    public function send($url, $data = '') {
        $body = $this->request($url, $data);
        return @json_decode($body, true);
    }

}
