<?php

namespace Delz\Phalcon;

use Phalcon\DiInterface;
use Phalcon\Di\ServiceInterface;

/**
 * IoC对象是对Di的封装
 *
 * 不用在每个类中注入Di对象，直接可以用静态方法调用容器的服务
 *
 * 在系统初始化的时候通过IoC::setDi($di)设置注入Di对象，在其它类中需要用到服务容器的服务可以不必
 * 每个类中注入Di对象，直接通过IoC::get($name)静态调用获取服务对象，也可以设置通过IoC::set()方法
 * 配置服务
 *
 * @package Delz\Phalcon
 */
class IoC
{
    /**
     * @var DiInterface
     */
    private static $di;

    /**
     * @return DiInterface
     */
    public static function getDi()
    {
        return self::$di;
    }

    /**
     * @param DiInterface $di
     */
    public static function setDi(DiInterface $di)
    {
        self::$di = $di;
    }

    /**
     * @param string $name
     * @param null|array $parameters
     * @return mixed
     * @throws \RuntimeException
     */
    public static function get($name, $parameters = null)
    {
        if (self::$di == null) {
            throw new \RuntimeException('IoC container is null!');
        }
        return self::$di->get($name, $parameters);
    }

    /**
     * @param string $name
     * @param mixed $definition
     * @param bool $shared
     * @return ServiceInterface
     * @throws \RuntimeException
     */
    public static function set($name, $definition, $shared = false)
    {
        if (self::$di == null) {
            throw new \RuntimeException('IoC container is null!');
        }
        return self::$di->set($name, $definition, $shared);
    }

    /**
     * @param string $name
     * @param mixed $definition
     * @return ServiceInterface
     */
    public static function setShared($name, $definition)
    {
        return self::set($name, $definition, true);
    }
}