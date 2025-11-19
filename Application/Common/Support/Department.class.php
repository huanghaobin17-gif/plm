<?php

namespace Common\Support;

/**
 * 科室
 */
class Department {

    use Singleton;

    /**
     * @var array[]
     */
    protected $map = [];

    public function __construct() {
        $departname = [];
        include APP_PATH . "Common/cache/department.cache.php";

        $this->map = $departname;
    }

    /**
     * 根据ID获取科室名称
     *
     * @return string|null
     */
    public function getName($id) {
        $department = $this->getMap()[$id];

        if (!$department) {
            return null;
        }

        return $department['department'];
    }

    /**
     * @return array[]
     */
    public function getMap() {
        return $this->map;
    }
}
