<?php

namespace Common\Support;

class UrlGenerator {

    use Singleton;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @param array $config
     */
    public function __construct($config = []) {
        if (C('USE_VUE_WECHAT_VERSION')) {
            $root = C('APP_NAME') . C('VUE_FOLDER_NAME') . '/#' . C('VUE_NAME');
        } else {
            $root = C('HTTP_HOST') . C('MOBILE_NAME');
        }

        $this->config = array_merge([
            'root' => $root,
        ], $config);
    }

    /**
     * Generate an absolute URL to the given path.
     *
     * @param string $path
     * @param array|object|null $params
     *
     * @return string
     */
    public function to($path, $params = []) {
        $root = $this->config['root'];
        $query = $params ? '?' . http_build_query($params) : '';

        return $root . $path . $query;
    }
}
