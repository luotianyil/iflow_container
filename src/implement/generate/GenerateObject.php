<?php

namespace iflow\Container\implement\generate;

use iflow\Container\implement\annotation\traits\Execute;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\interfaces\GenerateInterface;
use iflow\Container\implement\generate\traits\InvokeFunction;
use ReflectionClass;
use ReflectionException;
use Reflector;

class GenerateObject implements GenerateInterface {

    use InvokeFunction;

    /**
     * 实例化对象
     * @throws InvokeClassException|exceptions\InvokeFunctionException
     */
    public function make(string $class, array $vars = []): object {
        // TODO: Implement make() method.
        return $this->invokeClass($class, $vars);
    }

    /**
     * 反射执行方法类
     * @throws InvokeClassException|exceptions\InvokeFunctionException
     */
    public function invokeClass(string $class, array $vars = []): object {
        // TODO: Implement invokeClass() method.
        try {
            $execute = new Execute();
            $class = str_replace('\\\\', '\\', $class);
            $ref = new ReflectionClass($class);
            return $execute -> getReflectorAttributes($ref) -> execute($ref, $vars);
        } catch (ReflectionException $exception) {
            throw new InvokeClassException('Class not exists: ' . $class . '  ' . $exception -> getMessage());
        }
    }

    /**
     * 加载变量注解并执行
     * @param ReflectionClass|Reflector $reflectionClass
     * @param object $object
     * @param bool $initializer 是否为初始化注解执行
     * @return object
     */
    public function GenerateClassParameters(ReflectionClass|Reflector $reflectionClass, object $object, bool $initializer = false): object {
        foreach ($reflectionClass -> getProperties() as $property) {
            $args = [ $object ];
            $this->executePropertyAnnotation($property, $args, $initializer);
        }
        return $object;
    }
}