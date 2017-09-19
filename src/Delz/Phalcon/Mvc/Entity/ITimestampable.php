<?php

namespace Delz\Phalcon\Mvc\Entity;

/**
 * 某个数据模型需要有创建时间和更新时间可实现本接口
 *
 * @package Delz\Phalcon\Mvc\Entity
 */
interface ITimestampable
{
    /**
     * 返回创建时间
     *
     * @return \Datetime
     */
    public function getCreatedAt();

    /**
     * 返回最近修改时间
     *
     * @return \Datetime
     */
    public function getUpdatedAt();

    /**
     * 设置创建时间
     *
     * @param \Datetime $createdAt
     */
    public function setCreatedAt(\Datetime $createdAt = null);

    /**
     * 设置最近修改时间
     *
     * @param \Datetime $updatedAt
     */
    public function setUpdatedAt(\Datetime $updatedAt = null);
}