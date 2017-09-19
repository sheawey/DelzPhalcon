<?php

namespace Delz\Phalcon\Mvc\Controller;

use Phalcon\Mvc\Controller as BaseController;

/**
 * 控制器基类
 *
 * @package Delz\Phalcon\Mvc\Controller
 */
class Controller extends BaseController
{
    /**
     * 根据服务名称获取服务
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->getDI()->get($name);
    }

    /**
     * 执行事件
     *
     * @param string $name
     * @param null|object $source
     */
    public function fireEvent($name, $source = null)
    {
        return $this->getDI()->fireEvent($name, $source);
    }
}