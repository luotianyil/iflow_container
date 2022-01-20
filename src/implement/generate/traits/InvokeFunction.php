<?php

namespace iflow\Container\implement\generate\traits;

use iflow\Container\implement\annotation\traits\Execute;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use ReflectionProperty;

trait InvokeFunction {

    use GenerateParameters;

    /**
     * 反射执行方法
     * @param array|string|\Closure|ReflectionFunctionAbstract $callable
     * @param array $vars
     * @return mixed
     * @throws InvokeFunctionException
     * @throws InvokeClassException
     */
    public function invoke(array|string|\Closure|ReflectionFunctionAbstract $callable, array $vars = []): mixed {
        if ($callable instanceof ReflectionFunctionAbstract) {
            $vars = $this->GenerateBindParameters($callable, $vars);
            return $callable -> invokeArgs($vars);
        }

        if ($callable instanceof \Closure || (is_string($callable) && !str_contains($callable, '::'))) {
            return $this->invokeFunction($callable, $vars);
        } else {
            return $this->invokeMethod($callable, $vars);
        }
    }

    /**
     * 执行闭包方法
     * @param string|\Closure $callable
     * @param array $vars
     * @return mixed
     * @throws InvokeFunctionException|InvokeClassException
     */
    public function invokeFunction(string|\Closure $callable, array $vars = []): mixed {
        try {
            $ref = new \ReflectionFunction($callable);
            $vars = $this->GenerateBindParameters($ref, $vars);

            // 执行方法参数注解
            $argsParameters = [ 'parameters' => &$vars ];
            $this -> GenerateFunctionAnnotation($ref)
                -> GenerateFunctionParameters($ref, $argsParameters);

            return $callable(...$vars);
        } catch (\ReflectionException $exception) {
            throw new InvokeFunctionException("function does not exists: ". $exception -> getMessage());
        }
    }

    /**
     * 执行对象方法
     * @param array|string $methods
     * @param array $vars
     * @return mixed
     * @throws InvokeClassException
     */
    protected function invokeMethod(array|string $methods, array $vars = []): mixed {
        [$class, $methods] = is_array($methods) ? $methods : explode('::', $methods);
        try {
            $ref = new \ReflectionMethod($class, $methods);
            $vars = $this->GenerateBindParameters($ref, $vars);

            if (!$ref -> isPublic()) $ref -> setAccessible(true);

            // 执行方法参数注解
            $argsParameters = [ 'parameters' => &$vars, $class ];
            $this -> GenerateFunctionAnnotation($ref, $argsParameters)
                -> GenerateFunctionParameters($ref, $argsParameters);

            return $ref -> invokeArgs(is_object($class) ? $class : null, $vars);
        } catch (\ReflectionException $exception) {
            throw new \Error($exception -> getMessage() . ' LINE ' . $exception -> getLine());
        }
    }

    /**
     * 加载方法参数注解并执行
     * @param ReflectionFunctionAbstract $reflectionFunctionAbstract
     * @param $args
     * @return static
     */
    protected function GenerateFunctionParameters(ReflectionFunctionAbstract $reflectionFunctionAbstract, &$args): static {
        foreach ($reflectionFunctionAbstract -> getParameters() as $parameter) {
            $this->executePropertyAnnotation($parameter, $args);
        }
        return $this;
    }

    /**
     * 执行方法注解
     * @param ReflectionFunctionAbstract $reflectionFunctionAbstract
     * @param array $args
     * @return $this
     */
    protected function GenerateFunctionAnnotation(ReflectionFunctionAbstract $reflectionFunctionAbstract, array &$args = []): static {
        $this->executePropertyAnnotation($reflectionFunctionAbstract, $args);
        return $this;
    }


    /**
     * 获取参数注解
     * @param ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection
     * @param array $args
     * @param bool $initializer
     * @return array
     */
    protected function executePropertyAnnotation(
        ReflectionProperty|ReflectionParameter|ReflectionFunctionAbstract $reflection,
        array &$args = [],
        bool $initializer = false
    ): array {
        $executeLife = [ 'beforeCreate', 'Created', 'beforeMounted', 'Mounted', 'InitializerNonExecute' ];
        if ($initializer) array_pop($executeLife);
        $execute = new Execute();
        return $execute -> getReflectorAttributes($reflection)
            -> executeAnnotationLifeProcess($executeLife, $reflection, $args);
    }
}