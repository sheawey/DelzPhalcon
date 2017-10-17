<?php

namespace Delz\Phalcon\Command;

use Delz\Console\Contract\IInput;
use Delz\Console\Contract\IOutput;
use Delz\Config\IConfig;
use Delz\Phalcon\Ide\Generator;

/**
 * Ide类库生成器命令
 *
 * @package Delz\Phalcon
 */
class IdeGeneratorCommand extends DiAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(IInput $input = null, IOutput $output = null)
    {
        //获取配置参数ide.cphalcon_path和ide.output_path
        /** @var IConfig $config */
        $config = $this->getDi()->get("config");
        $cphalconPath = $config->get("ide.cphalcon_path");
        if(!is_dir($cphalconPath)) {
            $output->writeln(
                sprintf("<error>ide.cphalcon_path: %s is not exist.</error>", $cphalconPath)
            );
            return false;
        }

        $outputPath = $config->get("ide.output_path");
        if(!is_dir($outputPath) && !is_writable($outputPath)) {
            $output->writeln(
                sprintf("<error>ide.output_path: %s is not exist or can not writable.</error>", $outputPath)
            );
            return false;
        }

        $generator = new Generator($cphalconPath, $outputPath);
        $generator->make();

        $output->writeln("Done.");
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('ide:generate')
            ->setDescription("Ide类库生成器");
    }
}