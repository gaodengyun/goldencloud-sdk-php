<?php
/**
 * 开放平台接口调用通用类
 * @author  yancjie <yancjie@gmail.com>
 */
namespace Goldencloud;

class Client{

    private $config = [];
    private $handle = NULL;
    private $headers = [];
    private $response = [];

    /**
     * 配置初始化
     * Client constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config['host'] = $config['host'] ?? null;
        $this->config['appkey'] = $config['appkey'] ?? null;
        $this->config['secret'] = $config['secret'] ?? null;
        $this->config['route'] = $config['route'] ?? null;
        $this->config['timeout'] = $config['timeout'] ?? 5;

    }

    /**
     * 组装header数据
     * @return $this
     */
    private function _head()
    {
        $param['algorithm'] = 'HMAC-SHA256';
        $param['appkey'] = $this->config['appkey'];
        $param['nonce'] = mt_rand(100000, 999999);
        $param['timestamp'] = time();
        $srcStr = str_replace('&', '|', urldecode(http_build_query($param))) . '|' . $this->config['route'] . '|' . $this->payload;
        $signStr = base64_encode(hash_hmac('sha256', $srcStr, $this->config['secret'], true));
        $authorization = str_replace('&', ',', urldecode(http_build_query($param))) . ',signature=' . $signStr;
        $this->headers = [
            'Content-Type: application/json',
            'Authorization: ' . $authorization,
            'Content-Length: ' . strlen($this->payload)
        ];
        return $this;
    }

    private function _prepare()
    {
        $this->handle = curl_init();

        curl_setopt($this->handle, CURLOPT_POST, TRUE);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->payload);
        curl_setopt($this->handle, CURLOPT_URL, sprintf('%s%s', $this->config['host'], $this->config['route']));
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, $this->config['timeout']);
    }

    /**
     * @throws \Exception
     */
    private function _curl(){

        try {
            $response = curl_exec($this->handle);
            curl_close($this->handle);

            $this->response = json_decode($response, true);
        }catch (\Exception $e){
            throw new \Exception("无法获取有效数据", 500);
        }
    }

    /**
     * 发送 POST 请求
     *
     */
    public function postObject(array $arg)
    {
        if (! is_array($arg) || empty($arg)) {
            throw new \Exception( '请求参数不能为空', 400);
        }
        $this->payload = json_encode($arg);

        $this->_head();
        $this->_prepare();
        $this->_curl();

        return $this->response;
    }
}