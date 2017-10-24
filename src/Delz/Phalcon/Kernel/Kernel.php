<?php

namespace Delz\Phalcon\Kernel;

use Delz\Phalcon\Di;
use Delz\Phalcon\IoC;
use Phalcon\DiInterface;
use Delz\Config\IConfig;
use Delz\Config\Yaconf;
use Phalcon\Mvc\Model\MetaData\Apc as ApcMetaData;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Db\AdapterInterface;
use Phalcon\Crypt;
use Delz\Phalcon\Mvc\Model\Manager as ModelsManager;

/**
 * 应用核心抽象类
 *
 * @package Delz\Phalcon\Kernel
 */
abstract class Kernel implements IKernel
{
    /**
     * 依赖注入容器
     *
     * @var DiInterface
     */
    protected $di;

    /**
     * 应用标记
     *
     * 同一台服务器上名称需要唯一
     * 如果用到Yaconf配置类，配置文件名需取应用标记的名称
     *
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * 程序运行环境
     *
     * @var string
     */
    protected $environment;

    /**
     * 是否开启debug
     *
     * @var bool
     */
    protected $debug;

    /**
     * 程序开始运行时间
     *
     * 用于统计程序执行时间
     *
     * @var float
     */
    protected $startTime;

    /**
     * 内核文件路径
     *
     * @var string
     */
    protected $kernelFilePath;

    /**
     * 支持的环境
     */
    const ENVIRONMENTS = ['prod', 'dev'];

    /**
     * @param string $environment
     * @param bool $debug 是否开启debug
     */
    public function __construct($environment, $debug)
    {
        $this->environment = strtolower($environment);

        if (!in_array($this->environment, self::ENVIRONMENTS)) {
            throw new \RuntimeException('invalid environment. "prod" and "dev" is supported.');
        }

        $this->debug = (bool)$debug;

        if ($this->debug) {
            $this->startTime = microtime(true);
        }

        $this->appId = md5($this->getKernelFilePath() . $this->environment);
        $this->rootDir = $this->getRootDir();

        $this->di = new Di();
        IoC::setDi($this->di);

        //将kernel注册为内核服务
        $self = $this;
        $this->di->setShared('kernel', function () use ($self) {
            return $self;
        });

        //初始化一些基础服务
        $this->initConfigService();
        $this->initDbService();
        $this->initCryptService();
        $this->initEventsManagerService();
        $this->initModelsManagerService();
        $this->initModelsMetaDataService();
    }

    /**
     * 获取应用Id
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * {@inheritdoc}
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartTime()
    {
        return $this->debug ? $this->startTime : -INF;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return $this->getResourceDir() . '/cache';
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigDir()
    {
        return $this->getResourceDir() . '/config';
    }


    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return $this->getResourceDir() . '/log';
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir()
    {
        if ($this->rootDir === null) {
            $this->rootDir = dirname(dirname(dirname(dirname($this->getKernelFilePath()))));
        }

        return $this->rootDir;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppDir()
    {
        return $this->getRootDir() . '/public';
    }


    /**
     * 获取源码路径，一般指src目录
     *
     * @return string
     */
    public function getSourceDir()
    {
        return $this->getRootDir() . '/src';
    }

    /**
     * 资源文件夹默认放在项目目录上层目录的resource目录
     *
     * 可根据实际情况重写此方法调整
     *
     * @return string
     */
    public function getResourceDir()
    {
        return $this->getRootDir() . '/resource';
    }

    /**
     * 获取内核文件路径
     */
    public function getKernelFilePath()
    {
        if ($this->kernelFilePath === null) {
            $r = new \ReflectionObject($this);
            $this->kernelFilePath = $r->getFileName();
        }

        return $this->kernelFilePath;

    }

    /**
     * 初始化config服务
     */
    protected function initConfigService()
    {
        $self = $this;
        $this->di->setShared('config', function () use ($self) {

            return new Yaconf($self->appId);
        });
    }

    /**
     * 初始化模型管理器服务
     */
    protected function initModelsManagerService()
    {
        $self = $this;
        $this->di->set('modelsManager', function () use ($self) {
            /** @var IConfig $config */
            $config = $self->di->getShared('config');
            ModelsManager::setDefaultPrefix($config->get('db.prefix'));
            return new ModelsManager();
        });
    }

    /**
     * 初始化模型元数据服务
     */
    protected function initModelsMetaDataService()
    {
        $self = $this;
        $this->di->setShared('modelsMetadata', function () use ($self) {
            /** @var IConfig $config */
            $config = $self->di->getShared('config');
            return new ApcMetaData([
                "lifetime" => $config->get('db.metadata.lifetime'),
                'prefix' => $config->get('db.metadata.prefix')
            ]);
        });
    }

    /**
     * 初始化数据库服务
     *
     * 实现了主从读写分离
     *
     * 如果要做分表，根据业务细节，重新实现此方法
     */
    protected function initDbService()
    {
        $self = $this;

        /** @var IConfig $config */
        $config = $this->di->getShared('config');

        //获取使用的数据库适配器
        $adapterKey = strtolower($config->get('db.adapter'));

        $connections = (array)$config->get('db.connections');

        //如果有多个connections，那么第一个作为master，其余全部slave，做读写分离
        if (count($connections) > 1) {
            $this->di->setShared('dbMaster', function () use ($self, $adapterKey, $connections) {
                foreach ($connections as $connection) {
                    if ($connection) {
                        return $self->createDbAdapter($adapterKey, (array)$connection, $self);
                    }
                }
            });
            $this->di->setShared('dbSlave', function () use ($self, $adapterKey, $connections) {
                $slaveConnections = array_shift($connections);
                $slaveOptions = array_rand($slaveConnections);
                return $self->createDbAdapter($adapterKey, (array)$slaveOptions, $self);
            });

        } else {
            $this->di->setShared('db', function () use ($self, $adapterKey, $connections) {
                foreach ($connections as $connection) {
                    if ($connection) {
                        return $self->createDbAdapter($adapterKey, (array)$connection, $self);
                    }
                }
            });
        }

    }

    /**
     * 初始化事件管理器服务
     */
    protected function initEventsManagerService()
    {
        $this->di->setShared('eventsManager', function () {
            $eventsManager = new EventsManager();
            $eventsManager->enablePriorities(true);

            return $eventsManager;
        });
    }

    /**
     * 创建数据库连接对象
     *
     * @param string $adapterKey
     * @param array $options
     * @param $self
     * @return AdapterInterface
     */
    protected function createDbAdapter($adapterKey, array $options = [], $self)
    {
        $adapterKey = strtolower($adapterKey);

        /** @var array $dbAdapterMapping 支持的数据库 */
        $dbAdapterMapping = [
            'mysql' => 'Phalcon\Db\Adapter\Pdo\Mysql',
            'oracle' => 'Phalcon\Db\Adapter\Pdo\Oracle',
            'postgresql' => 'Phalcon\Db\Adapter\Pdo\Postgresql',
            'sqlite' => 'Phalcon\Db\Adapter\Pdo\Sqlite',
        ];

        if (!isset($dbAdapterMapping[$adapterKey])) {
            throw new \InvalidArgumentException(
                sprintf('Not support database adapter: %s', $adapterKey)
            );
        }

        $adapterClass = $dbAdapterMapping[$adapterKey];

        /** @var AdapterInterface $dbAdapter */
        $dbAdapter = new $adapterClass($options);

        //测试环境写入查询日志
        if ($this->isDebug()) {
            $eventsManager = $self->di->getShared('eventsManager');
            $logger = new FileLogger($self->getResourceDir() . '/log/db_query.log');
            $eventsManager->attach('db', function ($event, $dbAdapter) use ($logger) {
                if ($event->getType() == 'beforeQuery') {
                    $sqlVariables = $dbAdapter->getSQLVariables();
                    if (count($sqlVariables)) {
                        $query = str_replace(array('%', '?'), array('%%', "'%s'"), $dbAdapter->getSQLStatement());
                        $query = vsprintf($query, $sqlVariables);
                        $logger->log($query, \Phalcon\Logger::INFO);
                    } else {
                        $logger->log($dbAdapter->getSQLStatement(), \Phalcon\Logger::INFO);
                    }
                }
            });
            $dbAdapter->setEventsManager($eventsManager);
        }

        return $dbAdapter;
    }

    /**
     * 初始化加密服务
     */
    protected function initCryptService()
    {
        $self = $this;
        $this->di->setShared('crypt', function () use ($self) {
            /** @var IConfig $config */
            $config = $self->di->getShared('config');
            $key = $config->get('key');
            $crypt = new Crypt();
            $crypt->setKey($key);
            return $crypt;
        });
    }
}