<?php

namespace Delz\Phalcon\Kernel;

use Phalcon\DiInterface;

/**
 * 应用内核接口类
 *
 * @package Delz\Phalcon\Kernel
 */
interface IKernel
{
    /**
     * 获取容器
     *
     * @return DiInterface
     */
    public function getDi();

    /**
     * 启动应用
     * @return mixed
     */
    public function boot();

    /**
     * 获取运行环境（开发 or 生产 或者其它）
     *
     * @return string
     */
    public function getEnvironment();

    /**
     * 是否开启debug
     *
     * @return bool
     */
    public function isDebug();

    /**
     * 获取程序开始时间（只有在debug模式下有效）
     *
     * @return float
     */
    public function getStartTime();

    /**
     * 获取缓存目录
     *
     * @return string
     */
    public function getCacheDir();

    /**
     * 获取日志存储目录
     *
     * @return string
     */
    public function getLogDir();

    /**
     * 获取资源文件目录
     *
     * @return string
     */
    public function getResourceDir();

    /**
     * 获取项目目录
     *
     * @return string
     */
    public function getAppDir();
}