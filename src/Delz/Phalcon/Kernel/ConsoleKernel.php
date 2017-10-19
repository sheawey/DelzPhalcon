<?php

namespace Delz\Phalcon\Kernel;

use Delz\Console\Command\Pool;
use Delz\Config\IConfig;
use Delz\Console\Contract\ICommand;
use Delz\Console\Contract\IInput;
use Delz\Console\Input\ArgvInput;
use Delz\Console\Output\Stream;
use Delz\Phalcon\Command\IdeGeneratorCommand;
use Delz\Phalcon\Command\ListCommand;

/**
 * 控制台内核
 *
 * @package Delz\Phalcon\Kernel
 */
class ConsoleKernel extends Kernel
{
    /**
     * @var IInput
     */
    protected $commandInput;

    /**
     * 初始化commandInput，并获取运行环境
     *
     * 默认运行环境是开发环境 dev
     *
     * @param bool $debug 是否开启debug
     */
    public function __construct($debug = false)
    {
        if (php_sapi_name() !== 'cli') {
            throw new \RuntimeException("can not run this script outside of cli");
        }
        $this->commandInput = new ArgvInput();
        if ($this->commandInput->hasArgument('env')) {
            $environment = strtolower($this->commandInput->getArgument('env'));
            if (!in_array($environment, self::ENVIRONMENTS)) {
                throw new \RuntimeException(
                    sprintf("invalid environment: %s", $environment)
                );
            }
        } else {
            $environment = 'dev';
        }
        parent::__construct($environment, $debug);
        $this->initCommandPoolService();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $commandOutput = new Stream();
        //第一个参数为命令名称
        $commandName = $this->commandInput->getFirstArgument();
        if(is_null($commandName)) {
            //显示所有命令
            $commandOutput->writeln("usage: " . $this->commandInput->getName() . "\t[command] [<args>]");
            $commandOutput->writeln("Command list:");
            foreach ($this->di->get("commandPool")->all() as $k => $v) {
                $commandOutput->writeln("<comment>$k</comment>\t" . $v->getDescription());
            }
        } else {
            if (!$this->di->get("commandPool")->has($commandName)) {
                $commandOutput->writeln("<error>command: " . $commandName . " not exist</error>");
            } else {
                /** @var ICommand $command */
                $command = $this->di->get("commandPool")->get($commandName);
                $command->run($this->commandInput, $commandOutput);
            }
        }
    }

    /**
     * 初始化命令容器服务
     */
    protected function initCommandPoolService()
    {
        /** @var IConfig $config */
        $config = $this->di->getShared('config');

        $self = $this;

        $this->di->setShared(
            "commandPool",
            function () use ($config, $self) {
                $pool = new Pool();
                //加入一些系统服务
                $pool->add(new ListCommand());
                if ($self->getEnvironment() == 'dev') {
                    $pool->add(new IdeGeneratorCommand());
                }
                $commands = $config->get("commands");
                if (is_array($commands) && count($commands) > 0) {
                    foreach ($commands as $command) {
                        $pool->add(new $command());
                    }
                }
                return $pool;
            }
        );
    }


}