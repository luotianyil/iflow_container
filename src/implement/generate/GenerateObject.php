<?php

namespace iflow\Container\implement\generate;

use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\interfaces\GenerateInterface;
use iflow\Container\implement\generate\traits\InvokeFunction;

class GenerateObject implements GenerateInterface {

    use InvokeFunction;

    public function make(string $class, array $vars = []): object {
        // TODO: Implement make() method.
        return $this->invokeClass($class, $vars);
    }

    /**
     * 反射执行方法类
     * @throws InvokeClassException
     */
    public function invokeClass(string $class, array $vars = []): object {
        // TODO: Implement invokeClass() method.
        try {
            $class = str_replace('\\\\', '\\', $class);
            $ref = new \ReflectionClass($class);

            if ($ref -> hasMethod('__make')) {
                $method = $ref -> getMethod('__make');
                if ($method -> isPublic() && $method -> isStatic()) {
                    $vars = $this->GenerateBindParameters($method, $vars);
                    $method -> invokeArgs(null, $vars);
                }
            }

            $constructor = $ref -> getConstructor();
            $vars = $constructor ? $this->GenerateBindParameters($constructor, $vars) : [];

            return $ref -> newInstanceArgs($vars);
        } catch (\ReflectionException $exception) {
            throw new InvokeClassException('Class not exists: ' . $class . $exception -> getMessage());
        }
    }
}