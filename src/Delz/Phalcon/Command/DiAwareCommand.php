<?php

namespace Delz\Phalcon;

use Delz\Console\Command\Command;
use Phalcon\DiInterface;
use Delz\Phalcon\IoC;

/**
 * 注入了Di的Command抽象类
 *
 * @package Delz\Phalcon
 */
abstract class DiAwareCommand extends Command
{
    /**
     * @var DiInterface
     */
    protected $di;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->di = IoC::getDi();
        parent::__construct();
    }

    /**
     * @return DiInterface
     */
    protected function getDi()
    {
        return $this->di;
    }

}