<?php

namespace iflow\Container\implement\annotation\abstracts;

use iflow\Container\implement\annotation\implement\enum\AnnotationEnum;
use iflow\Container\implement\annotation\interfaces\AnnotationInterface;

abstract class AnnotationAbstract implements AnnotationInterface {

    /**
     * 注解执行顺序枚举
     * @var AnnotationEnum
     */
    public AnnotationEnum $hookEnum = AnnotationEnum::beforeCreate;

    /**
     * 获取扩展参数内对象
     * @param array $args
     * @return object|null
     */
    protected function getObject(array $args = []): ?object {
        $length = count($args);
        if ($length === 0) return null;
        return $args[$length - 1];
    }


    /**
     * 获取参数数据
     * @param array $args
     * @return mixed
     */
    protected function getParameters(array $args = []): mixed {
        return $args['parameters'] ?? [];
    }

}