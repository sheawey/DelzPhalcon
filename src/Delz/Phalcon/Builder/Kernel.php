<?php

namespace Delz\Phalcon\Builder;

use Delz\Common\Util\Str;

/**
 * 内核生成器
 *
 * @package Delz\Phalcon\Builder
 */
class Kernel implements IBuilder
{
    /**
     * 类名
     *
     * @var string
     */
    protected $className;

    /**
     * 内核名称
     *
     * @var string
     */
    protected $name;

    /**
     * @param array $options 内核参数
     * @throws BuilderException
     */
    public function __construct($options = [])
    {
        if (!isset($options['name']) && !preg_match('#^[a-zA-Z][a-zA-Z0-9_-]+$#', $options['name'])) {
            throw new BuilderException("内核参数name没有设置或者非法，参数字母开头，支持字母、数字和_-符号");
        }

        $this->name = Str::studly($options['name']);
        $this->className = $this->name . 'Kernel';
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {

    }

}