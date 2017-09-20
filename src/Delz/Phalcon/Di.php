<?php

namespace Delz\Phalcon;

use Delz\Phalcon\Exception\ServiceNotExistException;
use Phalcon\Di as BaseDi;
use Phalcon\Events\Event;

/**
 * 对Phalcon Di进行扩展
 *
 * 将服务容器参数数组化，用Yaconf保存相关服务的配置参数，需要调用的时候将其set到服务器容器，然后get获取他。
 *
 * 这样避免了容器的开销，是容器按需加载，内存最小化。
 *
 * 在容器实例化的时候在框架中必须实例化config服务作为基础服务。
 *
 * @package Delz\Phalcon
 */
class Di extends BaseDi
{
    /**
     * 已绑定监听的事件
     *
     * @var array
     */
    protected $resolvedEvents = [];

    /**
     * 重写服务获取
     *
     * @param string $name
     * @param mixed $parameters
     * @return mixed
     */
    public function get($name, $parameters = null)
    {
        //系统注册的服务，直接用系统注册的服务
        if ($this->has($name)) {
            return parent::get($name, $parameters);
        }
        if (class_exists($name)) {
            return parent::get($name, $parameters);
        }
        //如果没有系统注册了服务，从服务配置文件中查找，并注册服务，返回服务

        $configServiceKey = 'services.' . $name;
        if (!parent::get('config')->has($configServiceKey)) {
            throw new ServiceNotExistException(
                sprintf('Service with name %s not exist.', $name)
            );
        }
        $serviceParameters = parent::get('config')->get($configServiceKey);
        $this->set($name, $serviceParameters, true);
        return parent::get($name, $parameters);
    }

    /**
     * 执行事件
     *
     * @param string $name
     * @param null|object $source
     */
    public function fireEvent($name, $source = null)
    {
        if(substr_count($name, ".") !== 1) {
            throw new \RuntimeException("Invalid event name");
        }

        $eventName = str_replace(".", ":", $name);
        //获取事件名称
        if (!isset($this->resolvedEvents[$eventName])) {
            $this->resolveEvent($eventName, $source);
        }

        $this->get("eventsManager")->fire($eventName, $source, null, true);
    }

    protected function resolveEvent($eventName, $source)
    {

        $eventKey = "events." . str_replace(":", ".", $eventName);

        if (parent::get('config')->has($eventKey)) {
            $listenerParameters = parent::get('config')->get($eventKey);

            $listeners = explode(",", $listenerParameters);
            foreach ($listeners as $listener) {

                //第一个参数是服务名称，第二个参数是方法名，第三个参数是权重，没有默认是100
                $listenerParameters = explode(":", $listener);

                if(!isset($listenerParameters[1])) {
                    throw new \RuntimeException("event parameters error，no method set");
                }
                $serviceName = $listenerParameters[0];
                $method = $listenerParameters[1];
                $priority = isset($listenerParameters[2]) ? (int)$listenerParameters[2] : 100;
                //注册监听事件
                $this->get("eventsManager")->attach($eventName, function(Event $event) use($source, $serviceName, $method) {
                    $service = $this->get($serviceName);
                    if($source == null) {
                        $service->$method($event);
                    } else {
                        $service->$method($event, $source);
                    }

                }, $priority);
            }
        }
        $this->resolvedEvents[$eventName] = true;
    }
}