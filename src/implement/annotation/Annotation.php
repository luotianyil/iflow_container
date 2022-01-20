<?php

namespace iflow\Container\implement\annotation;

use Attribute;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\initializer\FileSystem;
use iflow\Container\implement\annotation\traits\Cache;
use iflow\Container\implement\annotation\traits\Execute;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use ReflectionClass;
use ReflectionException;
use Reflector;

#[Attribute(
    Attribute::TARGET_CLASS|Attribute::TARGET_METHOD|Attribute::TARGET_FUNCTION
)]
class Annotation extends AnnotationAbstract {

    use Cache;

    protected array $defaultConfig = [
        // 需要读取的目录 <root_dir_path>/<dir_name*>
        'namespaces' => [],
        // 目录缓存
        'cache' => false,
        // 缓存地址
        'cache_path' => __DIR__ . '/runtime/annotation/iflow_annotation'
    ];

    protected array $classes = [];

    public function __construct(array $config = []) {
        $this->config = array_merge($this->defaultConfig, array_change_key_case($config));
    }

    /**
     * 初始化项目
     * @return void
     * @throws exceptions\CacheException|ReflectionException|InvokeFunctionException|InvokeClassException
     */
    protected function initializer(): void {
        if ($this->getCache()) $this->classes = $this->getCacheContent();
        if (empty($this->classes)) $this->readClass();

        foreach ($this->classes as $class) {
            $refClass = new ReflectionClass($class);
            if ($refClass -> isTrait() || $refClass -> isAbstract()) continue;
            (new Execute()) -> getReflectorAttributes($refClass) -> execute($refClass, [], true);
        }
    }

    /**
     * 加载指定目录下PHP类文件
     * @return array
     * @throws exceptions\CacheException
     */
    protected function readClass(): array {
        $file = new FileSystem();
        foreach ($this->config['namespaces'] as $path) {
            $files = $file -> loadFileList($path, '.php', true);
            $this->filePathToClass($files, projectRoot: dirname($path));
        }
        if ($this->getCache()) $this->saveCache($this -> classes);
        return $this->classes;
    }

    /**
     * 将PHP 文件转为 PSR4 可加载规范类
     * @param array $files
     * @param string $nameSpace
     * @param string $projectRoot
     * @return array
     */
    protected function filePathToClass(array $files = [], string $nameSpace = '', string $projectRoot = ''): array {
        foreach ($files as $key => $value) {
            if (is_array($value)) {
                if (sizeof($value) > 0) $this->filePathToClass($value, $nameSpace.'\\'.$key, $projectRoot);
            } elseif (file_exists($value)) {
                $class = str_replace([ '.php', $projectRoot, '/' ], [ '', '', '\\' ], $value);
                if (class_exists($class) || !in_array($class, $this->classes)) $this->classes[] = $class;
            }
        }
        return $this->classes;
    }

    /**
     * 注解执行入口
     * @param Reflector $reflector
     * @param ...$args
     * @return Reflector
     * @throws ReflectionException
     * @throws exceptions\CacheException
     */
    public function process(Reflector $reflector, &$args): Reflector {
        // TODO: Implement process() method.
        $this->initializer();
        return $reflector;
    }
}