<?php

namespace Delz\Phalcon\Mvc\Router\Annotation;

use Delz\Phalcon\IoC;
use Delz\Phalcon\Mvc\Router\RouteCollection;
use Phalcon\Annotations\Adapter\Memory as MemoryAdapter;
use Phalcon\Annotations\Reflection;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Annotations\Annotation;
use Phalcon\Mvc\Router\Route;

/**
 * 注释路由控制器文件读取类
 *
 * @package Delz\Phalcon\Mvc\Router\Annotation
 * @todo 注入事件，支持beforeMatch
 */
class Reader implements InjectionAwareInterface
{
    /**
     * 注释解析器
     *
     * @var MemoryAdapter
     */
    protected $reader;

    /**
     * @var DiInterface
     */
    protected $di;

    /**
     * 控制器后缀
     *
     * @var string
     */
    protected $controllerSuffix = 'Controller';

    /**
     * 方法后缀
     *
     * @var string
     */
    protected $actionSuffix = 'Action';

    /**
     * 默认命名空间
     *
     * @var string
     */
    protected $namespace;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->setDI(IoC::getDi());
        $this->reader = new MemoryAdapter();
    }

    /**
     * 对某个控制器类进行解析，返回路由集合对象RouteCollection
     *
     * @param string $className 类名
     * @return RouteCollection
     */
    public function parse($className)
    {
        if (!is_subclass_of($className, "Delz\\Phalcon\\Mvc\\Controller\\Controller")) {
            throw new \InvalidArgumentException(
                sprintf("%s must be subclass of Delz\\Phalcon\\Mvc\\Controller\\Controller", $className)
            );
        }

        $routeCollection = new RouteCollection();

        /** @var Reflection $reflection */
        $reflection = $this->reader->get($className);

        //获取类注释
        $classAnnotations = $reflection->getClassAnnotations();

        //初始化路由前缀
        $routerPrefix = '';

        //处理类注释
        if ($classAnnotations !== false) {
            /** @var Annotation $annotation */
            foreach ($classAnnotations as $annotation) {
                if ($annotation->getName() == 'RoutePrefix') {
                    $routerPrefix = $annotation->getArgument(0);
                }
            }
        }

        //处理方法注释
        $methods = $reflection->getMethodsAnnotations();
        if ($methods !== false) {
            foreach ($methods as $action => $methodCollection) {
                foreach ($methodCollection->getAnnotations() as $annotation) {
                    $route = $this->parseMethodAnnotation($className, $action, $routerPrefix, $annotation);
                    if (!is_null($route)) {
                        $routeCollection->add($route);
                    }
                }
            }
        }

        return $routeCollection;
    }

    /**
     * 处理方法注释
     *
     * @param string $controller 方法控制器名称
     * @param string $action 方法名
     * @param string $routerPrefix 路由前缀
     * @param Annotation $annotation 注释对象
     * @return null|Route 处理成功，返回Route对象，没有，返回null
     * @throws \InvalidArgumentException 注释解析错误，返回异常
     */
    protected function parseMethodAnnotation($controller, $action, $routerPrefix, Annotation $annotation)
    {
        $name = $annotation->getName();
        $isRoute = false;
        $methods = null;
        switch ($name) {
            case 'Route':
                $isRoute = true;
                break;
            case 'Get':
                $isRoute = true;
                $methods = 'GET';
                break;
            case 'Post':
                $isRoute = true;
                $methods = 'POST';
                break;
            case 'Put':
                $isRoute = true;
                $methods = 'PUT';
                break;
            case 'Patch':
                $isRoute = true;
                $methods = 'PATCH';
                break;
            case 'Delete':
                $isRoute = true;
                $methods = 'DELETE';
                break;
            case 'Options':
                $isRoute = true;
                $methods = 'OPTIONS';
                break;
        }

        if ($isRoute === true) {
            if (!$annotation->hasArgument(0)) {
                throw new \InvalidArgumentException(
                    sprintf("Invalid router annotation in %s:%s, there is no pattern set.", $controller, $action)
                );
            }
            $pattern = $this->normalizeRouter($routerPrefix) . $this->normalizeRouter($annotation->getArgument(0));

            if (!$pattern) {
                $pattern = '/';
            }

            $actionName = preg_replace('#' . preg_quote($this->actionSuffix) . '$#', '', $action);
            $controllerName = preg_replace('#' . preg_quote($this->controllerSuffix) . '$#', '', $controller);
            //去掉控制器defaultNamespace部分
            if (!is_null($this->namespace)) {
                $controllerName = preg_replace('#^' . preg_quote($this->namespace . '\\') . '#', '', $controllerName);
            }

            $paths = [
                'controller' => $controllerName,
                'action' => $actionName
            ];

            if (is_null($methods)) {
                if (!$annotation->hasArgument('methods')) {
                    throw new \InvalidArgumentException(
                        sprintf("Invalid router annotation in %s:%s, there is no methods set.", $controller, $action)
                    );
                }
                $methods = $annotation->getArgument('methods');
            }

            $route = new Route($pattern, $paths, $methods);

            if ($annotation->hasArgument('name')) {
                $route->setName($annotation->getArgument('name'));
            }

            if ($annotation->hasArgument('hostname')) {
                $route->setHostname($annotation->getArgument('hostname'));
            }

            return $route;

        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setDI(DiInterface $di)
    {
        $this->di = $di;
    }

    /**
     * {@inheritdoc}
     */
    public function getDI()
    {
        return $this->di;
    }

    /**
     * 设置控制器后缀
     *
     * @param string $controllerSuffix
     */
    public function setControllerSuffix($controllerSuffix)
    {
        $this->controllerSuffix = $controllerSuffix;
    }

    /**
     * 设置方法后缀
     *
     * @param string $actionSuffix
     */
    public function setActionSuffix($actionSuffix)
    {
        $this->actionSuffix = $actionSuffix;
    }

    /**
     * 设置命名空间
     *
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * 标准化Router
     *
     * @param string $router
     * @return string
     */
    private function normalizeRouter($router)
    {
        $router = trim($router, "/");
        return $router ? '/' . $router : $router;
    }


}