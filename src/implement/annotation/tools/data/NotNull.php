<?php

namespace iflow\Container\implement\annotation\tools\data;

use Attribute;
use iflow\Container\Container;
use iflow\Container\implement\annotation\tools\data\abstracts\DataAbstract;
use iflow\Container\implement\annotation\tools\data\exceptions\ValueException;
use iflow\Container\implement\generate\exceptions\InvokeClassException;
use ReflectionException;
use Reflector;

#[Attribute(Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class NotNull extends DataAbstract {

    public function __construct(
        protected mixed $value = "",
        protected string $error = ""
    ) {}

    /**
     * @param Reflector $reflector
     * @param ...$args
     * @return bool
     * @throws InvokeClassException
     * @throws ReflectionException
     * @throws ValueException
     */
    public function process(Reflector $reflector, &$args): bool {
        // TODO: Implement process() method.
        try {
            // 获取初始化值
            $object = $reflector instanceof \ReflectionParameter ? null : $this->getObject($args);
            $value = $this->getValue($reflector, $object, $args);

            $type = Container::getInstance() -> getParameterType($reflector);

            if (in_array('array', $type) && empty($type)) $this->throw_error($reflector);
            if (!is_null($value) && $value !== '') return true;
            $this->throw_error($reflector);
        } catch (\Error) {
            $this->throw_error($reflector);
        }
    }
}