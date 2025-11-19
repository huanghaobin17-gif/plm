<?php
namespace Admin\Common;
class CiAnTongAPI
{
    private $baseURL;
    private $headers;

    // 访问那边的接口要key
    public $key = "2dbbe0cf-799c-4a11-a4f8-05497f9509de";

    public function __construct($baseURL = "http://iotapi.kang-cloud.com")
    {
        $this->baseURL = $baseURL;
        $this->headers = array(
            'Content-Type: application/x-www-form-urlencoded',
        );
    }

    public function sendRequest($endpoint, $data)
    {
        $url = $this->baseURL . $endpoint;
        $postData = http_build_query($data);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            // 处理错误
            return false;
        } else {
            // 处理成功响应
            curl_close($ch);
            return $response;
        }


    }
}