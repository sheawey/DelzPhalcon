<?php

namespace Delz\Phalcon\Builder;

use Delz\Common\Util\File;
use Delz\Common\Util\Str;
use Delz\Phalcon\Builder\Common\IBuilder;
use Delz\Phalcon\Builder\Common\BuilderException;

/**
 * 内核生成器
 *
 * @package Delz\Phalcon\Builder
 */
class Kernel implements IBuilder
{
    /**
     * 类名
     *
     * @var string
     */
    protected $className;

    /**
     * 内核名称
     *
     * @var string
     */
    protected $name;

    /**
     * 命名空间
     *
     * @var string
     */
    protected $namespace;

    /**
     * 内核文件路径
     *
     * @var string
     */
    protected $path;

    /**
     * @param array $options 内核参数
     * @throws BuilderException
     */
    public function __construct($options = [])
    {
        if (!isset($options['name']) || !preg_match('#^[a-zA-Z][a-zA-Z0-9_-]*$#', $options['name'])) {
            throw new BuilderException("内核参数name没有设置或者非法，参数字母开头，支持字母、数字和_-符号");
        }

        if (!isset($options['namespace']) || !preg_match("#^[a-zA-Z][a-zA-Z0-9_\\\\]*$#", $options['namespace'])) {
            throw new BuilderException("命名空间namespace没有设置或者非法，参数字母开头，支持字母、数字和_\\符号");
        }

        if (!isset($options['path'])) {
            throw new BuilderException("文件路径没有设置");
        }

        $this->name = Str::studly($options['name']);
        $this->className = $this->name . 'Kernel';
        $this->namespace = $options['namespace'];
        $this->path = rtrim($options['path'], DIRECTORY_SEPARATOR);
    }

    /**
     * {@inheritdoc}
     */
    public function build()
    {
        $content = <<<EOT
<?php
namespace $this->namespace;

use Delz\Phalcon\Kernel\HttpKernel as Kernel;

class $this->className {
EOT;

        $content .= PHP_EOL . '}';

        if(!File::write($this->path . DIRECTORY_SEPARATOR . $this->className .'.php', $content)) {
            throw new BuilderException("无法创建内核类");
        }
    }

}