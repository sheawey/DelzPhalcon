<?php

namespace Delz\Phalcon\Mvc;

use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Events\Manager as EventsManager;
use Delz\Phalcon\Mvc\Model\Manager as ModelManager;

/**
 * 对Phalcon\Mvc\Model二次封装
 *
 * @package Delz\Phalcon\Mvc
 */
class Model extends PhalconModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $tableName;

    /**
     * 是否读写分离
     *
     * @var bool
     */
    protected $isMasterSlave = false;

    /**
     * 初始化一些东西
     *
     * 找到所有__init开头的方法，执行它
     */
    public function initialize()
    {
        $eventsManager = new EventsManager();
        $this->setEventsManager($eventsManager);
        //获取所有方法
        $r = new \ReflectionObject($this);
        $allMethods = $r->getMethods();
        foreach($allMethods as $method) {
            if(substr($method->getName(), 0, 6) === '__init') {
                call_user_func([$this, $method->getName()]);
            }
        }
    }

    /**
     * 在这里设置表名前缀
     *
     * 默认由Delz\Phalcon\Mvc\Model\Manager::getDefaultPrefix()配置
     *
     * @return string
     */
    public function getPrefix()
    {
        return ModelManager::getDefaultPrefix();
    }

    /**
     * 不允许子类对其更新
     *
     * 表名必须根据规定的方式
     *
     * @return string
     */
    final public function getSource()
    {
        if(!$this->tableName) {
            $this->tableName = parent::getSource();
        }
        return $this->getPrefix() . $this->tableName;
    }

}