<?php

namespace iflow\Container\implement\annotation\interfaces;

use Reflector;

interface AnnotationInterface {
    public function process(Reflector $reflector, &$args): mixed;
}