<?php

namespace Delz\Phalcon\Command;

use Delz\Console\Contract\IInput;
use Delz\Console\Contract\IOutput;
use Delz\Common\Util\Dir;

/**
 * 清空缓存命令
 *
 * @package Delz\Phalcon\Command
 */
class CacheClearCommand extends DiAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(IInput $input = null, IOutput $output = null)
    {
        $cacheDir = $this->di->get('kernel')->getCacheDir();

        if(Dir::delete($cacheDir)) {
            $output->writeln("清空缓存成功！");
        } else {
            $output->writeln("<error>清空缓存失败，请检查目录是否存在或者权限是否可写。</error>");
        }
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('cache:clear')
            ->setDescription("清空缓存");
    }
}