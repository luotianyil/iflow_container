<?php

namespace iflow\Container\implement\annotation\tools\tool;

use Attribute;
use iflow\Container\Container;
use iflow\Container\implement\annotation\abstracts\AnnotationAbstract;
use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use Reflector;

#[Attribute(Attribute::TARGET_CLASS)]
class Callback extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    public function __construct(protected string $classes = '', protected string $method = '') {
    }

    public function process(Reflector $reflector, &$args): mixed {
        // TODO: Implement process() method.
        $object = $this->getObject($args);

        if (function_exists($this->method)) {
            call_user_func($this->method, $reflector, $object, $args);
        }

        if (!class_exists($this->classes)) throw new \Exception('Callback class does not exists', 502);

        if ($this->classes === $reflector -> getName()) {
            return call_user_func([new $this -> classes, $this->method], $reflector, $object, $args);
        }

        $_args = [$reflector, $object, $args];
        $container = Container::getInstance();
        return $container -> invoke([$container -> make($this -> classes), $this->method], $_args);
    }
}