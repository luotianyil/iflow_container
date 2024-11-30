<?php

namespace iflow\Container;

use iflow\Container\implement\generate\GenerateObject;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use iflow\Container\implement\annotation\exceptions\AttributeTypeException;
use Psr\Container\ContainerInterface;

class Container extends GenerateObject implements ContainerInterface {

    /**
     * 实例化对象容器管理
     * @var \WeakMap
     */
    protected \WeakMap $containers;

    /**
     * 已实例化对象列表
     * @var array
     */
    protected array $bind = [];

    /**
     * 当前容器
     * @var Container|null
     */
    protected static ?Container $instance = null;

    public function __construct() {
        $this->containers ??= new \WeakMap();
        $this->initializer();
    }

    /**
     * 初始化类
     * @return void
     */
    public function initializer(): void {}

    /**
     * 获取当前容器
     * @return static
     */
    public static function getInstance(): static {
        if (is_null(static::$instance)) static::$instance = new static();
        if (static::$instance instanceof \Closure) return (new static())();
        return self::$instance;
    }

    /**
     * 实例化对象
     * @param string|class-string $class 类名
     * @param array $vars 构造函数参数
     * @param bool $isNew 是否重新生成对象
     * @param callable|null $call 实例化后回调参数
     * @return object
     * @throws InvokeClassException|InvokeFunctionException|AttributeTypeException
     */
    public function make(string $class, array $vars = [], bool $isNew = false, ?callable $call = null): object {

        if ($isNew) $this->delete($class);

        // 验证当前容器是否存在该对象
        if ($this->has($class)) return $this->get($class);

        // 实例化对象
        $this->bind[$class] = new \stdClass();
        $object = parent::make($class, $vars);
        $this->containers -> offsetSet($this->bind[$class], $object);
        return $call ? $call($object) : $object;
    }

    /**
     * 向容器内部注册对象
     * @param string|class-string $name 对象名称
     * @param object $object
     * @param callable|null $call
     * @return mixed
     * @throws \Exception
     */
    public function register(string $name, object $object, ?callable $call = null): mixed {
        if (isset($this->bind[$name])) throw new \Exception('name already exists in the container');
        $this->bind[$name] = new \stdClass();
        $this->containers -> offsetSet($this->bind[$name], $object);
        return $call ? $call($object) : $object;
    }

    /**
     * 获取 容器 内对象
     * @param string|class-string $id
     * @return mixed
     */
    public function get(string $id): object {
        // TODO: Implement get() method.
        if ($this->has($id)) {
            return $this->containers -> offsetGet($this->bind[$id]);
        }
        throw new \Error('class does not exists in the container: '. $id);
    }

    /**
     * 验证容器内对象是否存在
     * @param string $id 类名
     * @return bool
     */
    public function has(string $id): bool {
        // TODO: Implement has() method.
        return !empty($this->bind[$id]) && $this->containers -> offsetExists($this->bind[$id]);
    }

    /**
     * 删除当前容器内对象
     * @param string $id
     * @return void
     */
    public function delete(string $id): void {
        if (!empty($this->bind[$id])) {
            $this->containers -> offsetUnset($this->bind[$id]);
            unset($this->bind[$id]);
        }
    }
}