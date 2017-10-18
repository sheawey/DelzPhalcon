<?php

namespace Delz\Phalcon\Command;

use Delz\Console\Contract\IInput;
use Delz\Console\Contract\IOutput;
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
        if ($input->hasArgument('cphalcon_path')) {
            $cphalconPath = $input->getArgument('cphalcon_path');
            $cphalconPath = realpath($this->di->get('kernel')->getKernelFilePath() . '/' . trim($cphalconPath, '/'));
        } else {
            $output->writeln("<error>cphalcon_path is not set.</error>");
            return false;
        }
        if (!is_dir($cphalconPath)) {
            $output->writeln(
                sprintf("<error>cphalcon_path: %s is not exist.</error>", $cphalconPath)
            );
            return false;
        }


        if ($input->hasArgument('output_path')) {
            $outputPath = $input->getArgument('output_path');
            $outputPath = realpath($this->di->get('kernel')->getKernelFilePath() . '/' . trim($outputPath, '/') . '/.ide');
        } else {
            $outputPath = realpath($this->di->get('kernel')->getAppDir() . '/.ide');
        }
        if (!is_dir($outputPath) && !is_writable($outputPath)) {
            $output->writeln(
                sprintf("<error>output_path: %s is not exist or can not writable.</error>", $outputPath)
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