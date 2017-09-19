<?php

namespace Delz\Phalcon\Mvc;

use Phalcon\Mvc\Url as PhalconUrl;

/**
 * 对Phalcon\Mvc\Url进行扩展
 *
 * @package Delz\Phalcon\Mvc
 */
class Url extends PhalconUrl
{
    /**
     * 静态文件版本
     *
     * @var string
     */
    protected $version;

    /**
     * 设置静态文件版本
     *
     * @param string $version 版本号
     */
    public function setStaticVersion($version)
    {
        $this->version = $version;
    }

    /**
     * 重写获取静态文件方法，加上版本号
     *
     * @param null|string $uri
     * @return string
     */
    public function getStatic($uri = null)
    {
        return parent::getStatic($uri) . '?' . $this->version;
    }
}