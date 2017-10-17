<?php

namespace Delz\Phalcon\Command;

use Delz\Console\Contract\IInput;
use Delz\Console\Contract\IOutput;

/**
 * 显示所有命令
 *
 * @package Delz\Phalcon
 */
class ListCommand extends DiAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(IInput $input = null, IOutput $output = null)
    {
        $output->writeln("Command list:");
        foreach ($this->di->get("commandPool")->all() as $k => $v) {
            $output->writeln("<comment>$k</comment>\t" . $v->getDescription());
        }
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('list')
            ->setDescription("显示所有命令");
    }
}