<?php

namespace iflow\Container\implement\annotation\tools\data;

use Attribute;
use iflow\Container\Container;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use ReflectionException;
use Reflector;

#[Attribute(Attribute::TARGET_PROPERTY|Attribute::TARGET_PARAMETER)]
class Inject extends Value {

    /**
     * 获取当前值是否存在并按照类型生成新的值
     * @param Reflector $ref
     * @param object|null $object
     * @param array $args
     * @return mixed
     * @throws ReflectionException|InvokeClassException
     */
    public function getValue(Reflector $ref, object|null $object = null, array &$args = []): mixed {
        $value = parent::getValue($ref, $object, $args['parameters']); // TODO: Change the autogenerated stub
        if (!$value) {
            $types = Container::getInstance() -> getParameterType($ref);
            if (count($types) > 0 && class_exists($types[0])) {
                $value = Container::getInstance() -> make($types[0]);
            }
        }
        return $value;
    }

}