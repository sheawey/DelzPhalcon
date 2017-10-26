<?php

namespace Delz\Phalcon\Builder;

/**
 * 生成器接口
 *
 * @package Delz\Phalcon\Builder
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