<?php

namespace Common\Weixin;

use Common\Support\Singleton;
use EasyWeChat\Core\Exceptions\HttpException;
use EasyWeChat\Foundation\Application;
use Exception;
use Think\Log;

class Weixin {

    use Singleton;

    /**
     * @var Application
     */
    protected $app;

    /**
     * @param array $config
     */
    public function __construct($config = []) {
        $config = array_merge([
            'app_id' => C('WX_APPID'),
            'secret' => C('WX_SECRET'),

            'debug' => true,
            'log'   => [
                'level' => isset($config['log_level']) ? $config['log_level'] : 'warning',
                'file'  => LOG_PATH . 'Common/easywechat.log',
            ],
        ], $config);

        /**
         * TODO 支持反向代理
         *
         * @see \Admin\Model\CommonModel::sendMesByWechat
         */

        $this->app = new Application($config);
    }

    /**
     * @param string $openId
     * @param string $templateName
     * @param array $data
     * @param string $url
     *
     * @return int|null 消息ID
     */
    public function sendMessage($openId, $templateName, $data, $url = null) {
        try {
            $templateId = $this->getTemplateId($templateName);
            $data = $this->formatData($data);

            $response = $this->getApp()->notice->send([
                'touser'      => $openId,
                'template_id' => $templateId,
                'url'         => $url,
                'data'        => $data,
            ]);

            return $response['msgid'];

        } catch (Exception $e) {
            Log::record($e->getMessage());

            return null;
        }
    }

    /**
     * 获取我的模板
     *
     * @return array[]
     * @throws HttpException
     */
    public function getTemplates() {
        $response = $this->getApp()->notice->getPrivateTemplates();

        // 逆序结果，使最新的模板排前
        return array_reverse($response['template_list']);
    }

    /**
     * @return Application
     */
    public function getApp() {
        return $this->app;
    }

    public function getTemplateId($templateName) {
        return C('WX_TEMPLATES')[$templateName];
    }

    /**
     * @param array $data
     *
     * @return string[]
     */
    protected function formatData($data) {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_null($value) || $value === '') {
                // 值为空，替换为占位符，防止消息发送失败
                switch (true) {
                    case strpos($key, 'time') === 0:
                        $value = '0000-00';
                        break;
                    case strpos($key, 'const') === 0:
                        $value = '';
                        break;
                    default:
                        $value = '-';
                        break;
                }
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
