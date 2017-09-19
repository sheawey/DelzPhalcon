<?php

namespace Delz\Phalcon\Mvc\Entity;

use Delz\Phalcon\Mvc\Model;
use Phalcon\Events\Event;

/**
 * 实现了ITimestampable接口的trait
 *
 * @package Delz\Phalcon\Mvc\Entity
 */
trait TTimestampable
{
    /**
     * 创建时间
     *
     * @var string
     */
    public $createdAt;

    /**
     * 最新修改时间
     *
     * @var string
     */
    public $updatedAt;

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return new \DateTime($this->createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        if(!$this->updatedAt) {
            return null;
        }
        return new \DateTime($this->updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        if($createdAt === null) {
            $this->createdAt = null;
        }
        $this->createdAt = $createdAt->format('Y-m-d H:i:s');
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        if($updatedAt === null) {
            $this->updatedAt = null;
        }
        $this->updatedAt = $updatedAt->format('Y-m-d H:i:s');
    }

    /**
     * 实现事件
     */
    protected function __initTimestampable()
    {
        if(!$this instanceof Model) {
            return;
        }
        $this->getEventsManager()->attach('model:beforeValidationOnCreate',function(Event $event, $model){
            $model->setCreatedAt(new \DateTime());
        });
        $this->getEventsManager()->attach('model:beforeValidationOnUpdate',function(Event $event, $model){
            $model->setUpdatedAt(new \DateTime());
        });
    }
}