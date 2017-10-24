<?php

namespace Delz\Phalcon\Kernel;

/**
 * 内核工厂类
 *
 * @package Delz\Phalcon\Kernel
 */
abstract class Factory
{
    /**
     * 内核数组
     *
     * 内核标记对应
     *
     * @var array
     */
    protected $kernels = [];

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->registerKernels();
    }

    /**
     * 创建应用内核
     *
     * @param string $kernelName
     * @param string $environment
     * @param bool $debug
     * @return IKernel
     */
    public function create($kernelName, $environment = 'dev', $debug = false)
    {
        $kernelClassName = $this->kernels[$kernelName];
        return new $kernelClassName($environment, $debug);
    }

    /**
     * 注册应用内核
     */
    abstract protected function registerKernels();
}