[![Php Version](https://img.shields.io/badge/php-%3E=8.1-brightgreen.svg)](https://secure.php.net/)

# iflow container

遵循 PSR-11 规范的容器组件

# 安装
```shell
composer require iflow/container
```

# 使用

```php
use iflow\Container\Container;

$container = Container::getInstance();

// 新建对象
$container -> make('class', ...$args, call: function ($object) { return $object });

// 将已实例化的对象写入容器
$container -> register('class', $obj);

// 获取容器对象
$container -> get('class');

// 验证当前容器是否存在改对象
$container -> has('class');

// 删除容器内对象
$container -> delete('class');
```

# 功能
- 容器 (PSR-11)
- 自定义注解模块
