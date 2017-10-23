<?php

namespace Delz\Phalcon\Mvc\Router;

use Phalcon\Mvc\Router\Route;
use Phalcon\Mvc\Router\RouteInterface;

/**
 * 路由对象集合
 *
 * @package Delz\Phalcon\Mvc\Router
 */
class RouteCollection implements \IteratorAggregate, \Countable
{
    /**
     * 路由对象数组
     *
     * @var array
     */
    private $routes = [];

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->routes);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->routes);
    }

    /**
     * 添加路由到集合
     *
     * @param RouteInterface $route
     */
    public function add(RouteInterface $route)
    {
        $name = $route->getName();
        if (is_null($name)) {
            $name = md5(strtolower($route->getPattern()));
        }

        $this->routes[$name] = $route;
        $route->setName($name);
    }

    /**
     * 取得所有路由
     *
     * @return array
     */
    public function all()
    {
        return $this->routes;
    }

    /**
     * 根据name获取路由
     *
     * @param string $name
     * @return mixed|null
     */
    public function get($name)
    {
        if (preg_match("#/#", $name)) {
            $name = md5(strtolower($name));
        }
        return isset($this->routes[$name]) ? $this->routes[$name] : null;
    }

    /**
     * 删除路由
     *
     * @param array|string $name 可以是单个路由，也可以是多个路由
     */
    public function remove($name)
    {
        foreach ((array)$name as $n) {
            if (preg_match("#/#", $n)) {
                $key = md5(strtolower($n));
            } else {
                $key = $n;
            }
            unset($this->routes[$key]);
        }
    }

    /**
     * 在当前集合追加一个集合
     *
     * @param RouteCollection $collection
     */
    public function append(RouteCollection $collection)
    {
        foreach ($collection->all() as $name => $route) {
            $this->routes[$name] = $route;
        }
    }

    /**
     * 将集合中Route对象转化成数组，便于缓存
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];
        /**
         * @var string $name
         * @var RouteInterface $route
         */
        foreach ($this->routes as $name => $route) {
            $result[$name] = [
                'pattern' => $route->getPattern(),
                'methods' => $route->getHttpMethods(),
                'paths' => $route->getPaths()
            ];

            if (!is_null($route->getHostname())) {
                $result[$name]['hostname'] = $route->getHostname();
            }
        }

        return $result;
    }

    /**
     * 将数组转换成Route对象加入集合
     *
     * @param array $array
     * @throws \InvalidArgumentException
     */
    public function loadFromArray($array)
    {
        if (!is_array($array)) {
            throw new \InvalidArgumentException("Invalid parameter, only array supported.");
        }

        $supportingKeys = ['pattern', 'methods', 'paths', 'hostname', 'name'];
        $arrayKeys = array_keys($array);
        if ($arrayKeys != array_intersect($arrayKeys, $supportingKeys) ||
            !isset($arrayKeys['pattern']) ||
            !isset($arrayKeys['methods']) ||
            !isset($arrayKeys['paths'])
        ) {
            throw new \InvalidArgumentException("Invalid parameter, only support keys: 'pattern', 'methods', 'paths', 'hostname'");
        }

        $route = new Route($array['pattern'], $array['paths'], $array['methods']);
        if (isset($array['name'])) {
            $route->setName($array['name']);
        }
        if (isset($array['hostname'])) {
            $route->setHostname($array['hostname']);
        }

        $this->add($route);

    }

}