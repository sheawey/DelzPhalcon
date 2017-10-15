<?php

namespace Delz\Phalcon;

use Delz\Console\Contract\IInput;
use Delz\Console\Contract\IOutput;

/**
 * 清空缓存命令
 *
 * @package Delz\Phalcon
 */
class ClearCacheCommand extends DiAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(IInput $input, IOutput $output)
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("cache:clear")
            ->setDescription("清空缓存");
    }


}