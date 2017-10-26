<?php

namespace Delz\Phalcon\Builder\Common;

/**
 * 生成器接口
 *
 * @package Delz\Phalcon\Builder\Common
 */
interface IBuilder
{
    /**
     * 执行生成动作
     *
     * 如果生成失败，抛出BuilderException异常
     *
     * @return mixed
     * @throw BuilderException
     */
    public function build();
}