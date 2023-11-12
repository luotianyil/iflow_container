<?php

namespace iflow\Container\implement\generate\traits;

use iflow\Container\Container;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use iflow\Container\implement\generate\exceptions\InvokeFunctionException;
use ReflectionNamedType;
use ReflectionFunctionAbstract;
use ReflectionProperty;
use ReflectionParameter;
use ReflectionType;
use Reflector;

trait GenerateParameters {

    /**
     * 生成绑定参数
     * @param ReflectionFunctionAbstract $method
     * @param array $vars
     * @return array
     * @throws InvokeClassException|InvokeFunctionException
     */
    public function GenerateBindParameters(ReflectionFunctionAbstract $method, array $vars = []): array {
        if (!$vars && $method -> getNumberOfParameters() === 0) return [];
        $parameters = $method -> getParameters();

        reset($vars);

        $type = key($vars) === 0 ? 1 : 0;
        $args = [];
        foreach ($parameters as $parameter) {
            $name = $parameter -> getName();
            $types = $this->getParameterType($parameter);
            if (count($types) > 0) {
                if (class_exists($types[0]))
                    $args[] = $this->getObjectParam($types[0], $vars);
                elseif (1 == $type && !empty($vars))
                    $args[] = array_shift($vars);
                elseif ($parameter -> isDefaultValueAvailable())
                    $args[] = $parameter -> getDefaultValue();
//                else throw new \Error('method '. $method -> getName() .' param miss:' . $name);
            }
        }
        return $args;
    }

    /**
     * 获取方法参数 返回实例化
     * @param string $className
     * @param array $vars
     * @return object
     * @throws InvokeClassException|InvokeFunctionException
     */
    public function getObjectParam(string $className, array &$vars): object {
        $value = array_shift($vars);
        return $value instanceof $className ? $value : Container::getInstance() -> make($className);
    }

    /**
     * 获取参数类型
     * @param ReflectionProperty|ReflectionParameter|Reflector $property
     * @param string $getTypeMethod
     * @return array
     */
    public function getParameterType(ReflectionProperty|ReflectionParameter|Reflector $property, string $getTypeMethod = 'getType'): array {
        $type = call_user_func([ $property, $getTypeMethod ]);
        $types = [];

        if ($type && method_exists($type, 'getTypes')) {
            $types = array_map(fn($info): string => $info -> getName(), $type -> getTypes());
        } else if ($type instanceof ReflectionNamedType) {
            $types[] = $type -> getName();
        }

        return $types ?: [ 'mixed' ];
    }

    /**
     * 类型转字符串
     * @param string $parameterName
     * @param array $type
     * @return string
     */
    public function parameterTypeToStr(string $parameterName = '', array $type = []): string {
        return sprintf('%s%s', implode('|', $type), $parameterName ? ' $'.$parameterName : '');
    }

    /**
     * 检测类型是否为接口或对象
     * @param string $typeName
     * @return string
     */
    public function checkTypeNameByClassOrInterface(string $typeName): string {
        try {
            $typeReflection = new \ReflectionClass($typeName);
            return $typeReflection -> isInterface() ? 'interface' : 'class';
        } catch (\ReflectionException $exception) {
            return $typeName;
        }
    }
}