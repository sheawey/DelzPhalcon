<?php

namespace Delz\Phalcon\Kernel;

use Delz\Console\Command\Pool;
use Delz\Config\IConfig;
use Delz\Console\Input\ArgvInput;
use Delz\Console\Output\Stream;
use Delz\Phalcon\IdeGeneratorCommand;

/**
 * 控制台内核
 *
 * @package Delz\Phalcon\Kernel
 */
class ConsoleKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        $this->initCommandPoolService();
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        if (php_sapi_name() !== 'cli') {
            throw new \RuntimeException("can not run this script outside of cli");
        }
        $input = new ArgvInput();
        $output = new Stream();
        $arguments = $input->getArguments();
        //如果没有参数，说明没有任何命令可执行，显示所有命令
        if (count($arguments) === 0) {
            $output->writeln("<error>please test \"list env=prod or list env=dev\" command.</error>");
        } else {
            //第一个参数为命令名称
            $commandName = array_shift($arguments);
            if (!$this->di->get("commandPool")->has($commandName)) {
                $output->writeln("<error>command: " . $commandName . " not exist</error>");
            } else {
                $command = $this->di->get("commandPool")->get($commandName);
                array_unshift($arguments, $commandName);
                $commandInput = new ArgvInput($arguments);
                $command->run($commandInput);
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
                if($self->getEnvironment() == 'dev') {
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