<?php

namespace Common\Support;

/**
 * 单例模式
 */
trait Singleton {

    /** @var static | null */
    protected static $instance = null;

    /**
     * 获取实例
     *
     * @return static
     */
    public static function instance($config = []) {
        if (static::$instance === null) {
            static::$instance = new static($config);
        }

        return static::$instance;
    }

    /**
     * 默认初始化方法
     *
     * 传递所有参数
     *
     * @param ...$args
     *
     * @return static
     */
    static protected function initialize(...$args) {
        return new static(...$args);
    }

    /**
     * 单例不能克隆
     */
    private function __clone() {
    }
}
