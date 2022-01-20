<?php

namespace iflow\Container\implement\annotation\traits;

use Attribute;
use iflow\Container\Container;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\exceptions\AttributeTypeException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Reflector;

class Execute {

    /**
     * 注解执行顺序
     * @var array|array[]
     */
    protected array $life = [
        'beforeCreate' => [],
        'Created' => [],
        'beforeMounted' => [],
        'Mounted' => [],
        'InitializerNonExecute' => []
    ];

    public function getReflectorAttributes(Reflector $reflection): static {
        if ($reflection -> getName() === Attribute::class) return $this;
        $this->readExecuteAnnotation($reflection);
        return $this;
    }

    /**
     * 初始化注解信息
     * @param Reflector $reflection
     * @param ReflectionAttribute $reflectionAttribute
     * @return AnnotationAbstract
     * @throws AttributeTypeException|ReflectionException
     */
    protected function process(Reflector $reflection, ReflectionAttribute $reflectionAttribute): AnnotationAbstract {
        $reflectionClass = new ReflectionClass($reflectionAttribute -> getName());
        $_attrObject = $reflectionClass -> newInstance(...$reflectionAttribute -> getArguments());
        if (!$_attrObject instanceof AnnotationAbstract) throw new AttributeTypeException('object instanceof AnnotationAbstract has valid fail className: '. $reflectionAttribute -> getName());
        return Container::getInstance() -> GenerateClassParameters($reflectionClass, $_attrObject);
    }

    /**
     * 实例化对象， 执行全部注解
     * @param Reflector $reflector
     * @param array $vars
     * @param bool $initializer
     * @return object
     * @throws InvokeClassException
     * @throws InvokeFunctionException
     */
    public function execute(Reflector $reflector, array $vars = [], bool $initializer = false): object {
        $container = Container::getInstance();
        $this -> executeAnnotationLifeProcess('beforeCreate', $reflector);

        // 初始化构造函数参数
        $constructor = $reflector -> getConstructor();
        $vars = $constructor ? $container->GenerateBindParameters($constructor, $vars) : [];
        $_obj = $reflector -> newInstanceArgs($vars);

        if (method_exists($_obj, '__make')) {
            $container -> invoke([$_obj, '__make'], [ $container, $reflector ]);
        }

        $args = [ $_obj ];

        // 执行创建回调以及挂载结束注解
        $this -> executeAnnotationLifeProcess(['Created', 'beforeMounted'], $reflector, $args);

        // 非初始化时挂在完毕后执行注解
        if (!$initializer) $this -> executeAnnotationLifeProcess('InitializerNonExecute', $reflector, $args);

        $_obj = $container -> GenerateClassParameters($reflector, $_obj, $initializer);

        $this -> executeAnnotationLifeProcess('Mounted', $reflector, $args);
        return $_obj;
    }

    /**
     * 获取当前可执行数据注解列表
     * @param Reflector $reflection
     * @return array
     * @throws AttributeTypeException|ReflectionException
     */
    protected function readExecuteAnnotation(Reflector $reflection): array {
        $attributes = $reflection -> getAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute -> getName() === Attribute::class) continue;
            $_attribute = $this->process($reflection, $attribute);
            $this -> life = $_attribute -> hookEnum -> getAnnotationLife($this -> life, $_attribute);
        }
        return $this->life;
    }

    /**
     * @return array
     */
    public function getLife(): array {
        return $this->life;
    }

    /**
     * 执行指定生命周期注解方法
     * @param string|array $lifeName
     * @param Reflector $reflectionClass
     * @param array $args
     * @return array
     */
    public function executeAnnotationLifeProcess(string|array $lifeName, Reflector &$reflectionClass, array &$args = []): array {
        if (!is_array($lifeName)) return array_map(
            function ($_attribute) use (&$reflectionClass, &$args) { $_attribute -> process($reflectionClass, $args); },
            $this->life[$lifeName]
        );

        $returns = [];
        foreach ($lifeName as $name) {
            $returns[] = array_map(
                function ($_attribute) use (&$reflectionClass, &$args) { $_attribute -> process($reflectionClass, $args); },
                $this->life[$name]);
        }
        return $returns;
    }
}