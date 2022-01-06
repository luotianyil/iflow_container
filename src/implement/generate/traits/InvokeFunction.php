<?php

namespace iflow\Container\implement\generate\traits;

use iflow\Container\implement\generate\exceptions\InvokeFunctionException;

trait InvokeFunction {

    use GenerateParameters;

    /**
     * 反射执行方法
     * @param array|string|\Closure $callable
     * @param array $vars
     * @return mixed
     * @throws InvokeFunctionException
     */
    public function invoke(array|string|\Closure|\ReflectionFunctionAbstract $callable, array $vars = []): mixed {
        if ($callable instanceof \ReflectionFunctionAbstract) {
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
     * @throws InvokeFunctionException
     */
    protected function invokeFunction(string|\Closure $callable, array $vars = []): mixed {
        try {
            $ref = new \ReflectionFunction($callable);
            $vars = $this->GenerateBindParameters($ref, $vars);
            return $callable(...$vars);
        } catch (\ReflectionException) {
            throw new InvokeFunctionException("function does not exists: $callable");
        }
    }

    /**
     * 执行对象方法
     * @param array|string $methods
     * @param array $vars
     * @return mixed
     */
    protected function invokeMethod(array|string $methods, array $vars = []): mixed {
        [$class, $methods] = is_array($methods) ? $methods : explode('::', $methods);
        try {
            $ref = new \ReflectionMethod($class, $methods);
            $args = $this->GenerateBindParameters($ref, $vars);

            if (!$ref -> isPublic()) $ref -> setAccessible(true);
            return $ref -> invokeArgs(is_object($class) ? $class : null, $args);
        } catch (\ReflectionException) {
            throw new \Error("function not exists: $methods / Class: $class");
        }
    }
}