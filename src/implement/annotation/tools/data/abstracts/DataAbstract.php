<?php

namespace iflow\Container\implement\annotation\tools\data\abstracts;

use Error;
use iflow\Container\Container;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\tools\data\exceptions\ValueException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;
use Reflector;

abstract class DataAbstract extends AnnotationAbstract {

    // 变量默认值
    protected mixed $default = '';

    protected string $error = '';

    /**
     * 获取当前值
     * @param Reflector $ref
     * @param object|null $object
     * @param array $args
     * @return mixed
     * @throws InvokeClassException
     * @throws ReflectionException
     */
    public function getValue(Reflector $ref, object|null $object = null, array &$args = []): mixed
    {
        $refIsProperty = $ref instanceof ReflectionProperty;
        try {
            if ($refIsProperty) return $ref -> getValue($object);

            $value = $args[$ref -> getPosition()] ?? $ref -> getDefaultValue();
            if (is_bool($value) || is_null($value) || is_numeric($value)) return $value;

            return $value ?: throw new \Exception('method miss params null');
        } catch (Error|\Exception) {
            if ($refIsProperty) return $ref -> getDefaultValue() ?: $this->defaultIsClass();
            // 当方法参数 不存在且无默认值时
            return (
                $ref -> isDefaultValueAvailable() ? $ref -> getDefaultValue() : null
            ) ?: $this->defaultIsClass();
        }
    }

    /**
     * 验证是否为类
     * @return mixed
     * @throws InvokeClassException
     */
    protected function defaultIsClass(): mixed {
        if (is_string($this->default) && class_exists($this->default)) {
            // 初始化类
            $this->default = Container::getInstance() -> make($this->default);
        }
        return $this->default;
    }

    /**
     * 获取默认值
     * @return mixed
     */
    public function getDefault(): mixed {
        return $this->default;
    }


    /**
     * 获取反射参数值
     * @param ReflectionProperty|ReflectionParameter $ref
     * @return mixed
     */
    protected function getRefDefaultValue(ReflectionProperty|ReflectionParameter $ref): mixed {
        if ($ref instanceof ReflectionProperty) {
            return $ref -> hasDefaultValue() ? $ref -> getDefaultValue() : null;
        }
        return $ref -> isDefaultValueAvailable() ? $ref -> getDefaultValue() : null;
    }

    /**
     * 抛出异常
     * @throws ValueException
     */
    protected function throw_error($ref) {
        throw new ValueException($this->error ?: "{$ref -> getName()} required");
    }

}