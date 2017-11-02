<?php

namespace Delz\Phalcon\Command;

use Delz\Console\Contract\IInput;
use Delz\Console\Contract\IOutput;

/**
 * 获取应用配置参数
 *
 * @package Delz\Phalcon\Command
 */
class AppConfigCommand extends DiAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(IInput $input = null, IOutput $output = null)
    {
        if (!$input->hasArgument('kernel')) {
            throw new \InvalidArgumentException("kernel not set");
        }
        $kernelClass = $input->getArgument('kernel');
        if (!class_exists($kernelClass)) {
            throw new \InvalidArgumentException(
                sprintf("class %s is not exist.", $kernelClass)
            );
        }
        $environment = $this->getDi()->get('kernel')->getEnvironment();
        /** @var HttpKernel $kernel */
        $kernel = new $kernelClass($environment, false);
        $kernelParameters = [
            'appId' => $kernel->getAppId(),
            'cacheDir' => $kernel->getCacheDir(),
        ];

        foreach($kernelParameters as $k=>$v) {
            $output->writeln("$k=$v");
        }
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('config')
            ->setDescription("获取应用配置参数");
    }
}