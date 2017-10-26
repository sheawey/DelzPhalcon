<?php

namespace Delz\Phalcon\Builder;

/**
 * 项目生成
 *
 * @package Delz\Phalcon\Builder
 */
class Project implements IBuilder
{
    /**
     * @param array $options 项目参数
     * @throws BuilderException
     */
    public function __construct($options = [])
    {
        if(!isset($options['name']) && !preg_match('#^[a-zA-Z][a-zA-Z0-9_-]+$#', $options['name'])) {
            throw new BuilderException("项目名称name没有设置或者非法，项目名称字母开头，支持字母、数字和_-符号");
        }


    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {

    }

}