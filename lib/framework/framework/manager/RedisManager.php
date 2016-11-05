<?php
namespace framework\manager;
use framework\config\RedisConfiguration;

/**
 * Redis 连接实例管理器
 *
 * @package framework\driver\redis
 */
class RedisManager
{
    /**
     * 连接配置
     *
     * @var <Config>array
     */
    private static $configs;
    /**
     * 连接实例
     *
     * @var <\Redis>array
     */
    private static $instances;

    /**
     * 添加配置
     *
     * @param int $index
     * @param Config $config
     */
    public static function addConfig($index, RedisConfiguration $config)
    {
        self::$configs[$index] = $config;
    }

    /**
     * 获取连接实例
     *
     * @param int $index
     * @return \Redis
     */
    public static function getInstance($index)
    {
        if (empty(self::$instances[$index]))
        {
            if (empty(self::$configs[$index]))
            {
                return null;
            }

            $config = self::$configs[$index];

            $redis = new \Redis();

            if ($config->socket)
            {
                $redis->connect($config->socket);
            }
            else
            {
                $redis->connect($config->host, $config->port);
            }

            if ($config->serialize)
            {
                $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            }

            if ($config->database > 0)
            {
                $redis->select($config->database);
            }

            self::$instances[$index] = $redis;
        }

        return self::$instances[$index];
    }
}
