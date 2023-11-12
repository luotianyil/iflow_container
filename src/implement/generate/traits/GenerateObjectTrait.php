<?php

namespace iflow\Container\implement\generate\traits;

trait GenerateObjectTrait {

    /**
     * 获取当前反射类函数 排除父类方法
     * @param \ReflectionClass $reflector
     * @return array
     */
    public function getMethodFilterParent(\ReflectionClass $reflector): array {

        $methods = $reflector -> getMethods();

        $filterMethods = [];

        foreach ($methods as $method) {
            if ($reflector -> getName() !== $method->class) continue;
            $filterMethods[] = $method;
        }

        return $filterMethods;
    }


}