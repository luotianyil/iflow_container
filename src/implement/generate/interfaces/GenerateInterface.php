<?php

namespace Iflow\Container\implement\generate\interfaces;

interface GenerateInterface {

    /**
     * 创建实例
     * @param string $class
     * @param array $vars
     * @param bool $isNew
     * @return object
     */
    public function make(string $class, array $vars = []): object;

    /**
     * 实例化对象
     * @param string $class
     * @param array $vars
     * @return object
     */
    public function invokeClass(string $class, array $vars = []): object;
}