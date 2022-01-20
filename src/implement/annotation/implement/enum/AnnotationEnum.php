<?php

namespace iflow\Container\implement\annotation\implement\enum;

enum AnnotationEnum: string {

    // 实例化前
    case beforeCreate = 'beforeCreate';

    // 实例化后
    case Created = 'Created';

    // 实例化参数
    case beforeMounted = 'beforeMounted';

    // 挂载完毕
    case Mounted = 'Mounted';

    // 无需执行
    case NonExecute = 'NonExecute';

    // 初始化项目时无需执行
    case InitializerNonExecute = 'InitializerNonExecute';

    public function getAnnotationLife(array $life, object $_self): array {
        match ($this) {
            AnnotationEnum::beforeCreate => array_push($life['beforeCreate'], $_self),
            AnnotationEnum::Created => array_push($life['Created'], $_self),
            AnnotationEnum::beforeMounted => array_push($life['beforeMounted'], $_self),
            AnnotationEnum::Mounted => array_push($life['Mounted'], $_self),
            AnnotationEnum::InitializerNonExecute => array_push($life['InitializerNonExecute'], $_self),
            AnnotationEnum::NonExecute => []
        };
        return $life;
    }
}