<?php

namespace Delz\Phalcon\Command;

use Delz\Common\Util\Dir;
use Delz\Common\Util\File;
use Delz\Console\Contract\IInput;
use Delz\Console\Contract\IOutput;
use Delz\Phalcon\Kernel\HttpKernel;
use Delz\Phalcon\Mvc\Router\Annotation\Reader;
use Delz\Phalcon\Mvc\Router\RouteCollection;

/**
 * 路由文件生成命令
 *
 * @package Delz\Phalcon\Command
 */
class RouterGenerateCommand extends DiAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function execute(IInput $input, IOutput $output)
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
        //获取控制器目录
        $sourceDir = $this->getDi()->get('kernel')->getSourceDir();
        $controllerDir = $sourceDir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $kernel->getDefaultRouterNamespace());
        //获取控制器目录所有控制器文件
        $controllerFiles = Dir::read($controllerDir, '#\.php$#');
        $routeCollection = new RouteCollection();
        $routerReader = new Reader();
        $routerReader->setNamespace($kernel->getDefaultRouterNamespace());
        foreach ($controllerFiles as $file) {
            //去掉$sourceDir
            $className = preg_replace('#^' . preg_quote($sourceDir . DIRECTORY_SEPARATOR) . '#', '', $file);
            $className = preg_replace('#\.php$#', '', $className);
            $className = str_replace(DIRECTORY_SEPARATOR, '\\', $className);
            $collection = $routerReader->parse($className);
            $routeCollection->append($collection);
        }

        $routerFile = $kernel->getConfigDir() . '/routing/main_' . $environment . '.php';
        $data = $routeCollection->toArray();
        $result = File::write($routerFile, '<?php ' . PHP_EOL . PHP_EOL . 'return ' . var_export($data, true) . ';');
        if ($result === false) {
            throw new \RuntimeException("write fail");
        }
        $output->write("<info>generated success.</info>");
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('router:generate')
            ->setDescription("生成路由");
    }

}