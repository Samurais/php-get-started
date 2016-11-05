<?php
namespace framework\config;

/**
 * Redis 配置信息
 *
 * @package framework\config
 */
class RedisConfiguration
{
    /**
     * 服务器地址
     *
     * @var string
     */
    public $host;
    /**
     * 服务器端口
     *
     * @var int
     */
    public $port;
    /**
     * UNIX套接字
     *
     * @var string
     */
    public $socket;
    /**
     * 默认数据库
     *
     * @var int
     */
    public $database = 0;
    /**
     * 是否序列化数据
     *
     * @var boolean
     */
    public $serialize = FALSE;//原来是 true bob edit 和后台redis同步

    /**
     * 构造函数
     * new Config("127.0.0.1", 6379);
     * new Config("/tmp/redis.sock", 0);
     *
     * @param string $host
     * @param int $port
     * @param int $database
     * @param bool $serialize
     */
    public function __construct($host, $port, $database = 0, $serialize = FALSE) //原来是 true bob edit 和后台redis同步
    {
        if ($port)
        {
            $this->host = $host;
            $this->port = $port;
            $this->socket = null;
        }
        else
        {
            $this->host = null;
            $this->port = null;
            $this->socket = $host;
        }

        $this->database = $database;
        $this->serialize = $serialize;
    }
}
