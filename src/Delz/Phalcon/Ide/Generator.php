<?php

namespace Delz\Phalcon\Ide;

use Phalcon\Version;

/**
 * Ide类库生成器
 *
 * @package Delz\Phalcon\Ide
 */
class Generator
{
    /**
     * cphalcon源码目录
     *
     * @var string
     */
    protected $cphalconDirectory;

    /**
     * 要导出实例代码的目录
     *
     * @var string
     */
    protected $exportDirectory;

    /**
     * 类方法的注释数组
     *
     * 结构如下：
     * <code>
     * [
     *     类名 => [
     *         方法1 => 注释1,
     *         方法2 => 注释2,
     *     ]
     * ]
     * </code>
     *
     * @var array
     */
    protected $docs = [];

    /**
     * 类的注释
     *
     * <code>
     * [
     *      类1 => 注释1,
     *      类2 => 注释2
     * ]
     * </code>
     *
     * @var array
     */
    protected $classDocs = [];

    /**
     * @param string $cphalconDirectory cphalcon源码目录
     * @param string $exportDirectory 要导出实例代码的目录
     */
    public function __construct($cphalconDirectory, $exportDirectory)
    {
        if (!extension_loaded("phalcon")) {
            throw new \RuntimeException("PHP extension:'phalcon' is not loaded.");
        }
        $this->cphalconDirectory = realpath($cphalconDirectory);
        $this->exportDirectory = realpath($exportDirectory);
        $this->scanSources();
    }

    /**
     * 生成相应类库文件
     */
    public function make()
    {
        //获取phalcon版本，作为文件目录
        $version = Version::get();
        $versionPieces = explode(" ", $version);
        $genVersion = $versionPieces[0];

        $allClasses = array_merge(get_declared_classes(), get_declared_interfaces());
        foreach ($allClasses as $className) {
            if (!preg_match('#^Phalcon#', $className)) {
                continue;
            }
            //获取namespace和类名
            $pieces = explode("\\", $className);
            $namespaceName = join("\\", array_slice($pieces, 0, count($pieces) - 1));
            $normalClassName = join('', array_slice($pieces, -1));

            $source = "<?php" . PHP_EOL . PHP_EOL;
            $source .= "namespace " . $namespaceName . ";" . PHP_EOL . PHP_EOL;

            //在注释docs和classDocs中类名和命名空间是以_分割的，如类A\B\C，存的是A_B_C
            $simpleClassName = str_replace("\\", "_", $className);

            //获取类注释
            if (isset($this->classDocs[$simpleClassName])) {
                $source .= $this->classDocs[$simpleClassName];
            }

            //获取类的类型：interface？abstract? final?
            $reflector = new \ReflectionClass($className);
            if ($reflector->isInterface()) {
                $classType = 'interface ';
            } else {
                if ($reflector->isAbstract()) {
                    $classType = 'abstract class ';
                } elseif ($reflector->isFinal()) {
                    $classType = 'final class ';
                } else {
                    $classType = 'class ';
                }
            }

            $source .= $classType . $normalClassName;

            $extends = $reflector->getParentClass();
            if ($extends) {
                $source .= ' extends \\' . $extends->getName();
            }
            $implements = $reflector->getInterfaceNames();
            if ($implements) {
                $source .= " implements \\" . implode(",\\", $implements);
            }

            $source .= PHP_EOL . "{";
            if ($className == 'Phalcon\Di\Injectable') {

                $source .= '
    /**
     * @var \Phalcon\Mvc\Dispatcher|\Phalcon\Mvc\DispatcherInterface
     */
    public $dispatcher;
    
    /**
     * @var \Phalcon\Mvc\Router|\Phalcon\Mvc\RouterInterface
     */
    public $router;
    
    /**
     * @var \Phalcon\Mvc\Url|\Phalcon\Mvc\UrlInterface
     */
    public $url;
    
    /**
     * @var \Phalcon\Http\Request|\Phalcon\HTTP\RequestInterface
     */
    public $request;
    
    /**
     * @var \Phalcon\Http\Response|\Phalcon\HTTP\ResponseInterface
     */
    public $response;
    
    /**
     * @var \Phalcon\Http\Response\Cookies|\Phalcon\Http\Response\CookiesInterface
     */
    public $cookies;
    
    /**
     * @var \Phalcon\Filter|\Phalcon\FilterInterface
     */
    public $filter;
    
    /**
     * @var \Phalcon\Flash\Direct
     */
    public $flash;
    
    /**
     * @var \Phalcon\Flash\Session
     */
    public $flashSession;
    
    /**
     * @var \Phalcon\Session\Adapter\Files|\Phalcon\Session\Adapter|\Phalcon\Session\AdapterInterface
     */
    public $session;
    
    /**
     * @var \Phalcon\Events\Manager
     */
    public $eventsManager;
    
    /**
     * @var \Phalcon\Db
     */
    public $db;
    
    /**
     * @var \Phalcon\Security
     */
    public $security;
    
    /**
     * @var \Phalcon\Crypt
     */
    public $crypt;
    
    /**
     * @var \Phalcon\Tag
     */
    public $tag;
    
    /**
     * @var \Phalcon\Escaper|\Phalcon\EscaperInterface
     */
    public $escaper;
    
    /**
     * @var \Phalcon\Annotations\Adapter\Memory|\Phalcon\Annotations\Adapter
     */
    public $annotations;
    
    /**
     * @var \Phalcon\Mvc\Model\Manager|\Phalcon\Mvc\Model\ManagerInterface
     */
    public $modelsManager;
    
    /**
     * @var \Phalcon\Mvc\Model\MetaData\Memory|\Phalcon\Mvc\Model\MetadataInterface
     */
    public $modelsMetadata;
    
    /**
     * @var \Phalcon\Mvc\Model\Transaction\Manager
     */
    public $transactionManager;
    
    /**
     * @var \Phalcon\Assets\Manager
     */
    public $assets;
    
    /**
     * @var \Phalcon\DI|\Phalcon\DiInterface
     */
    public $di;
    
    /**
     * @var \Phalcon\Session\Bag
     */
    public $persistent;
    
    /**
     * @var \Phalcon\Mvc\View|\Phalcon\Mvc\ViewInterface
     */
    public $view;
    
		';
            }

            //生成常量
            foreach ($reflector->getConstants() as $constant => $value) {
                $source .= PHP_EOL . "\t const " . $constant . " = " . $value . ";" . PHP_EOL;
            }

            //生成成员变量
            foreach ($reflector->getProperties() as $property) {
                if ($property->getDeclaringClass()->getName() == $className) {
                    $source .= PHP_EOL . "\t" . implode(" ", \Reflection::getModifierNames($property->getModifiers())) . ' $' . $property->getName() . ';' . PHP_EOL;
                }
            }

            //获取方法注释
            if (isset($this->docs[$simpleClassName])) {
                $methodDocs = $this->docs[$simpleClassName];
            } else {
                $methodDocs = [];
            }

            //生成方法

            foreach ($reflector->getMethods() as $method) {
                if ($method->getDeclaringClass()->getName() == $reflector->getName()) {
                    $source .= PHP_EOL;

                    //生成方法注释
                    if (isset($methodDocs[$method->getName()])) {
                        $methodDocs[$method->name] = str_replace(' Phalcon', ' \Phalcon', $methodDocs[$method->getName()]);
                        foreach (explode("\n", $methodDocs[$method->name]) as $commentPiece) {
                            $source .= "\t" . $commentPiece . "\n";
                        }
                    }

                    //生成方法修饰符
                    $modifiers = \Reflection::getModifierNames($method->getModifiers());
                    if ($reflector->isInterface()) {
                        $modifiers = array_intersect($modifiers, ['public', 'static']);
                    }

                    $source .= "\t" . implode(" ", $modifiers) . " function " . $method->getName() . "(";

                    //获取方法参数
                    $parameters = [];
                    foreach ($method->getParameters() as $parameter) {
                        $strParameter = '';
                        $hintedClass = $parameter->getClass();
                        if ($hintedClass) {
                            $strParameter .= '\\' . $hintedClass->getName() . ' ';
                        }

                        if ($parameter->isOptional()) {
                            if ($parameter->isDefaultValueAvailable()) {
                                $strParameter .= '$' . $parameter->getName() . ' = ' . $parameter->getDefaultValue();
                            } else {
                                $strParameter .= '$' . $parameter->getName() . ' = null';
                            }
                        } else {
                            $strParameter .= '$' . $parameter->getName();
                        }
                        $parameters[] = $strParameter;
                    }

                    $source .= implode(", ", $parameters) . ")";

                    if (!$reflector->isInterface() && !$method->isAbstract()) {
                        $source .= "{}";
                    } else {
                        $source .= ";";
                    }

                    $source .= PHP_EOL;

                }

            }


            $source .= PHP_EOL . "}";


            $path = $this->exportDirectory . '/' . $genVersion . '/' . str_replace("\\", DIRECTORY_SEPARATOR, $namespaceName);
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . DIRECTORY_SEPARATOR . $normalClassName . ".php", $source);
        }
    }

    /**
     * 获取类方法的注释
     *
     * @return array
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * 获取类的注释
     *
     * @return array
     */
    public function getClassDocs()
    {
        return $this->classDocs;
    }

    /**
     * 扫描cphalcon的源码目录，并获取
     */
    protected function scanSources()
    {
        $directory = $this->cphalconDirectory . DIRECTORY_SEPARATOR . "ext";
        /** @var \RecursiveDirectoryIterator[] $iterator */
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $item) {
            if ($item->getExtension() == 'c') {
                if (strpos($item->getPathname(), 'kernel') === false) {
                    $this->_getDocs($item->getPathname());
                }
            }
        }
    }

    /**
     * 对某个文件进行注释提取，分别写入docs和classDocs
     *
     * @param string $file
     * @return null|mixed
     */
    protected function _getDocs($file)
    {
        $firstDoc = true;
        $openComment = false;
        $nextLineMethod = false;
        $comment = '';
        foreach (file($file) as $line) {
            if (trim($line) == '/**') {
                $openComment = true;
            }
            if ($openComment === true) {
                $comment .= $line;
            } else {
                if ($nextLineMethod === true) {
                    if (preg_match('/^PHP_METHOD\(([a-zA-Z0-9\_]+), (.*)\)/', $line, $matches)) {
                        $this->docs[$matches[1]][$matches[2]] = trim($comment);
                        $className = $matches[1];
                    } else {
                        if (preg_match('/^PHALCON_DOC_METHOD\(([a-zA-Z0-9\_]+), (.*)\)/', $line, $matches)) {
                            $this->docs[$matches[1]][$matches[2]] = trim($comment);
                            $className = $matches[1];
                        } else {
                            if ($firstDoc === true) {
                                $classDoc = $comment;
                                $firstDoc = false;
                                $comment = '';
                            }
                        }
                    }
                    $nextLineMethod = false;
                } else {
                    $comment = '';
                }
            }
            if ($openComment === true) {
                if (trim($line) == '*/') {
                    $openComment = false;
                    $nextLineMethod = true;
                }
            }
            if (preg_match('/^PHALCON_INIT_CLASS\(([a-zA-Z0-9\_]+)\)/', $line, $matches)) {
                $className = $matches[1];
            }
        }
        if (isset($classDoc)) {
            if (!isset($className)) {
                return null;
            }
            if (!isset($this->classDocs[$className])) {
                $this->classDocs[$className] = $classDoc;
            }
        }
    }
}