<?php

namespace Delz\Phalcon\Mvc\Model;

use Phalcon\Mvc\Model\Manager as PhalconModelManager;

/**
 * 对phalcon模型管理器二次包装
 *
 * @package Delz\Phalcon\Mvc\Model
 */
class Manager extends PhalconModelManager
{
    /**
     * 默认表前缀
     *
     * @var null|string
     */
    protected static $defaultPrefix = null;

    /**
     * 设置表默认前缀
     *
     * @param string $prefix
     */
    public static function setDefaultPrefix($prefix)
    {
        self::$defaultPrefix = $prefix;
    }

    /**
     * 获取默认表前缀
     *
     * @return null|string
     */
    public static function getDefaultPrefix()
    {
        return self::$defaultPrefix;
    }
}