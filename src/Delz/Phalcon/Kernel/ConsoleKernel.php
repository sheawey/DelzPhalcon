<?php

namespace Delz\Phalcon\Kernel;

use Delz\Console\Command\Pool;
use Delz\Config\IConfig;
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
    protected $input;

    /**
     * {@inheritdoc}
     */
    public function __construct(IInput $input, $environment, $debug)
    {
        parent::__construct($environment, $debug);
        $this->input = $input;
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
        $output = new Stream();
        $arguments = $this->input->getArguments();
        //如果没有参数，说明没有任何命令可执行，显示所有命令
        if (count($arguments) === 0) {
            $output->writeln("usage: " . $this->input->getName() . "\t[command] [<args>]");
            $output->writeln("Command list:");
            foreach ($this->di->get("commandPool")->all() as $k => $v) {
                $output->writeln("<comment>$k</comment>\t" . $v->getDescription());
            }
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
                $pool->add(new ListCommand());
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