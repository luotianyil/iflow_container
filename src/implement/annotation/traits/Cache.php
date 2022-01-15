<?php

namespace iflow\Container\implement\annotation\traits;

use iflow\Container\implement\annotation\exceptions\CacheException;

trait Cache {

    protected array $config = [];

    /**
     * 获取缓存状态
     * @return bool
     */
    public function getCache(): bool {
        return $this->config['cache'] ?? false;
    }

    /**
     * 获取缓存地址
     * @return string
     * @throws CacheException
     */
    public function getCachePath(): string {
        return $this->config['cache_path'] ?? throw new CacheException('缓存文件地址不能为空');
    }

    /**
     * 写入缓存
     * @param array $classes
     * @return bool
     * @throws CacheException
     */
    public function saveCache(array $classes): bool {
        $path = str_replace("\\", '/', $this -> getCachePath());
        !is_dir(dirname($path)) && mkdir(dirname($path), recursive: true);
        $content = serialize($classes);
        return file_put_contents($path, $content);
    }

    /**
     * 获取缓存数据
     * @return array
     * @throws CacheException
     */
    public function getCacheContent(): array {
        $path = $this -> getCachePath();
        $content = file_exists($path) ? file_get_contents($path) : "";
        return $content ? unserialize($content) : [];
    }
    
}