<?php

namespace Delz\Phalcon\Kernel;

use Phalcon\Mvc\Application;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Router;
use Delz\Phalcon\Mvc\Url;
use Delz\Config\IConfig;
use Phalcon\Http\Response\Cookies;
use Phalcon\Session\Adapter\Redis as Session;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Http\Request;
use Phalcon\Http\Response;

/**
 * web应用内核类
 *
 * @package Delz\Phalcon\Kernel
 */
class HttpKernel extends Kernel
{
    /**
     *
     * @var Application
     */
    protected $application;

    /**
     * {@inheritdoc}
     */
    public function __construct($environment, $debug)
    {
        //解决网址大小写问题，将网址全部转化成小写
        if (isset($_GET['_url'])) {
            $_GET['_url'] = strtolower($_GET['_url']);
        }
        parent::__construct($environment, $debug);
        //初始化web服务
        $this->initViewService();
        $this->initRoutingService();
        $this->initUrlService();
        $this->initCookieService();
        $this->initSessionService();
        $this->initDispatcherService();
    }

    /**
     * {@inheritdoc}
     */
    public function getApplication()
    {
        if ($this->application) {
            return $this->application;
        }
        $this->application = new Application();
        $this->application->setDI($this->di);
        $this->application->setEventsManager($this->di->get('eventsManager'));

        return $this->application;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $response = $this->getApplication()->handle();
        return $response;
    }

    /**
     * 获取模板路径
     *
     * @return string
     */
    protected function getViewDir()
    {
        return $this->getAppDir() . '/View/';
    }

    /**
     * 初始化Request服务
     */
    protected function initRequestService()
    {
        $this->di->setShared(
            "request",
            function () {
                return new Request();
            }
        );
    }

    /**
     * 初始化Response服务
     */
    protected function initResponseService()
    {
        $this->di->setShared(
            "response",
            function () {
                return new Response();
            }
        );
    }

    /**
     * 初始化路由服务
     */
    protected function initRoutingService()
    {
        $self = $this;
        $this->di->setShared('router', function () use ($self) {
            /** @var IConfig $config */
            $config = $self->di->getShared('config');
            $routeFile = $self->getResourceDir() . '/routers.php';
            $routers = [];
            if (file_exists($routeFile)) {
                $routers = include($routeFile);
            }
            $router = new Router(false);
            $router->setDefaultNamespace($config->get('router.default_namespace'));
            $router->setDefaultController($config->get('router.default_controller'));
            $router->setDefaultAction($config->get('router.default_action'));
            $router->removeExtraSlashes(true);
            if ($notFoundController = $config->get('router.404_page.controller') && $notFoundAction = $config->get('router.404_page.action')) {
                $router->notFound((array)$config->get('router.404_page'));
            }

            foreach ($routers as $url => $route) {
                if (count($route) !== count($route, COUNT_RECURSIVE)) {
                    if (isset($route['pattern']) && isset($route['paths'])) {
                        $method = isset($route['method']) ? $route['method'] : null;
                        //将网址转化成小写，解决网址大小写问题
                        $router->add(strtolower($route['pattern']), $route['paths'], $method);
                    } else {
                        throw new \RuntimeException(
                            sprintf('No route pattern and paths found by route %s', $url)
                        );
                    }
                } else {
                    $router->add($url, $route);
                }
            }
            return $router;

        });
    }

    /**
     * 初始化网址处理服务
     */
    protected function initUrlService()
    {
        $self = $this;
        $this->di->setShared('url', function () use ($self) {
            /** @var IConfig $config */
            $config = $self->di->getShared('config');
            $url = new Url();
            $url->setBaseUri($config->get('base_url'));
            $url->setStaticVersion($config->get('assets.version'));
            $url->setStaticBaseUri($config->get('assets.base_uri'));
            return $url;
        });
    }

    /**
     * 初始化视图服务
     */
    protected function initViewService()
    {
        $self = $this;
        $this->di->setShared(
            "voltService",
            function ($view, $di) use ($self) {
                $volt = new Volt($view, $di);
                $volt->setOptions(
                    [
                        "compiledPath" => $self->getResourceDir() . "/cache/view/",
                        "compiledExtension" => ".compiled",
                    ]
                );
                return $volt;
            }
        );

        $this->di->setShared('view', function () use ($self) {
            $view = new View();
            $view->setViewsDir($self->getViewDir());
            $view->registerEngines(
                [
                    ".volt" => "voltService",
                ]
            );
            return $view;
        });
    }

    /**
     * 初始化cookie服务
     */
    protected function initCookieService()
    {
        $this->di->setShared(
            "cookies",
            function () {
                $cookies = new Cookies();
                $cookies->useEncryption(true);
                return $cookies;
            }
        );
    }

    /**
     * 初始化分发器服务
     */
    protected function initDispatcherService()
    {
        $this->di->setShared(
            "dispatcher",
            function () {
                $dispatcher = new Dispatcher();
                return $dispatcher;
            }
        );
    }

    /**
     * 初始化session服务
     */
    protected function initSessionService()
    {
        $this->di->setShared(
            "session",
            function () {
                $session = new Session([
                    'uniqueId' => 'sjs',
                    'host' => 'localhost',
                    'port' => 6379,
                    'persistent' => false,
                    'lifetime' => 3600,
                    'prefix' => 'sjs_',
                    'index' => 1,
                ]);

                $session->start();
                return $session;
            }
        );
    }
}