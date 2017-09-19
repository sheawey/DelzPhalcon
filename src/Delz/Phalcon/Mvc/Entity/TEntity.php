<?php

namespace Delz\Phalcon\Mvc\Entity;

/**
 * IEntity的trait
 *
 * @package Delz\Phalcon\Mvc\Entity
 */
trait TEntity
{
    /**
     * Id.
     *
     * @var mixed
     */
    protected $id;

    /**
     * 获取主键
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}