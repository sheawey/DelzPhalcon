<?php

namespace Delz\Phalcon\Mvc\Entity;

/**
 * 实体接口
 *
 * 任何实体最好实现本接口
 *
 * 接口只有一个主键$id,可以在这里配置默认主键生成策略
 *
 * @package Delz\Phalcon\Mvc\Entity
 */
interface IEntity
{
    /**
     * 获取实体主键
     *
     * @return int|string 根据不同的主键生成策略而定
     */
    public function getId();
}