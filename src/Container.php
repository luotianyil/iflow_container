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
     * @var array<string, object>
     */
    protected array $containers = [];

    /**
     * 别名
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * 已实例化对象列表
     * @var array<string, string>
     */
    protected array $bind = [];

    /**
     * 当前容器
     * @var Container|\Closure|null
     */
    protected static Container|\Closure|null $instance = null;

    public function __construct() {
        $this->initializer();
    }

    /**
     * 初始化类
     * @return void
     */
    public function initializer(): void {}

    /**
     * 设置当前容器
     * @param Container|\Closure $instance
     * @return void
     */
    public static function setInstance(Container|\Closure $instance): void {
        self::$instance = $instance;
    }

    /**
     * 获取当前容器
     * @return static
     */
    public static function getInstance(): static {
        if (is_null(static::$instance)) static::$instance = new static();
        if (static::$instance instanceof \Closure) self::$instance = (static::$instance)();
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
        return $this -> instance($class, parent::make($class, $vars), $call);
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
        if (isset($this->containers[$name])) throw new \Exception('name already exists in the container');
        return $this -> instance($name, $object, $call);
    }

    /**
     * 重新设置实例化对象
     * @param string $name
     * @param object $object
     * @param callable|null $call
     * @return mixed
     */
    public function instance(string $name, object $object, ?callable $call = null): mixed {
        $this->containers[$this -> getAlias($name)] = $object;
        return $call ? $call($object) : $object;
    }

    public function setAlias(string $alias, string $className): void {
        $this->aliases[$alias] = $className;
    }

    public function getAlias(string $alias): string {
        return $this->aliases[$alias] ?? $alias;
    }

    /**
     * 获取 容器 内对象
     * @param string|class-string $id
     * @return object
     * @throws AttributeTypeException
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     */
    public function get(string $id): object {
        // TODO: Implement get() method.
        $id = $this -> getAlias($id);
        if ($this->has($id)) {
            return $this->containers[$id];
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
        return isset($this->containers[$this -> getAlias($id)]);
    }

    /**
     * 删除当前容器内对象
     * @param string $id
     * @return void
     */
    public function delete(string $id): void {
        $id = $this -> getAlias($id);
        if (!isset($this->containers[$id])) return;
        unset($this->containers[$id]);
    }

    public function __get(string $name) {
        return $this -> get($name);
    }

    public function __set(string $name, $value): void {
        $this -> instance($name, $value);
    }

    public function __unset(string $name): void {
        // TODO: Implement __unset() method.
        $this -> delete($name);
    }

    public function __isset(string $name): bool {
        // TODO: Implement __isset() method.
        return $this -> has($name);
    }

}