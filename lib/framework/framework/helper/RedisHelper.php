<?php
namespace framework\helper;

/**
 * Redis 数据处理类
 *
 * @package framework\helper
 */
class RedisHelper
{
    /**
     * 连接实例
     *
     * @var \Redis
     */
    private $client;

    /**
     * 构造函数
     *
     * @param  $index
     * @return \framework\helper\RedisHelper
     */
    public function __construct($index)
    {
       // $this->client = Manager::getInstance($index);
    }

    public function setClient($client)
    {
        $this->client = $client;
    }
    /**
     * 取得连接实例
     *
     * @return \Redis
     */
    function getClient()
    {
        return $this->client;
    }

    /**
     * 获取符合查询模式的键值
     *
     * @param  string $pattern
     * @return array
     */
    public function keys($pattern)
    {
        return $this->client->keys($pattern);
    }

    /**
     * 设置指定键名的数据
     *
     * @param  string $key
     * @param  mixed $value
     * @param  int $expiration
     * @return bool
     */
    public function set($key, $value, $expiration = 0)
    {
        if ($expiration > 0)
        {
            return $this->client->setex($key, $expiration, $value);
        }
        else
        {
            return $this->client->set($key, $value);
        }
    }

    /**
     * 设置多个键名的数据
     *
     * @param  array $items <key:value>
     * @return bool
     */
    public function setMulti($items)
    {
        return $this->client->mset($items);
    }

    /**
     * 设置指定键名的数据并返回原数据
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    public function getSet($key, $value)
    {
        return $this->client->getSet($key, $value);
    }

    /**
     * 获取指定键名的数据
     *
     * @param  string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->client->get($key);
    }

    /**
     * 获取指定键名序列的数据
     *
     * @param  array $keys
     * @return array
     */
    public function getMulti($keys)
    {
        $values = $this->client->getMultiple($keys);
        return array_combine($keys, $values);
    }

    /**
     * 增加指定键名的值并返回结果
     *
     * @param  string $key
     * @param  int $step
     * @return int
     */
    public function increase($key, $step = 1)
    {
        return $this->client->incr($key, $step);
    }

    /**
     * 删除指定键名的数据
     *
     * @param  string|array $key
     * @return int
     */
    public function delete($key)
    {
        if($this->exists($key))
        {
            return $this->client->del($key);
        }
    }

    /**
     * 判断指定键名是否存在
     *
     * @param  string $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->client->exists($key);
    }

    /**
     * 设置指定键名的过期时间
     *
     * @param  string $key
     * @param  int $timestamp
     * @return bool
     */
    public function expire($key, $timestamp)
    {
        return $this->client->expireAt($key, $timestamp);
    }

    /**
     * 设置指定哈希指定属性的数据
     *
     * @param  string $key
     * @param  string $prop
     * @param  mixed $value
     * @return bool|int
     */
    public function hashSet($key, $prop, $value)
    {
        return $this->client->hSet($key, $prop, $value);
    }

    /**
     * 设置指定哈希多个属性的数据
     *
     * @param  string $key
     * @param  array $props <prop:value>
     * @return bool
     */
    public function hashSetMulti($key, $props)
    {
        return $this->client->hMset($key, $props);
    }

    /**
     * 获取指定哈希指定属性的数据
     *
     * @param  string $key
     * @param  string $prop
     * @return bool|string
     */
    public function hashGet($key, $prop)
    {
        return $this->client->hGet($key, $prop);
    }

    /**
     * 获取指定哈希多个属性的数据
     *
     * @param  string $key
     * @param  array $props
     * @return array  <prop:value>
     */
    public function hashGetMulti($key, $props)
    {
        return $this->client->hMGet($key, $props);
    }

    /**
     * 删除指定哈希指定属性的数据
     *
     * @param  string $key
     * @param  string $prop
     * @return int
     */
    public function hashDel($key, $prop)
    {
        return $this->client->hDel($key, $prop);
    }

    /**
     * 获取指定哈希的长度
     *
     * @param  string $key
     * @return int
     */
    public function hashLen($key)
    {
        return $this->client->hLen($key);
    }

    /**
     * 获取指定哈希的所有属性
     *
     * @param  string $key
     * @return array
     */
    public function hashKeys($key)
    {
        return $this->client->hKeys($key);
    }

    /**
     * 获取指定哈希的所有属性的值
     *
     * @param  string $key
     * @return array
     */
    public function hashVals($key)
    {
        return $this->client->hVals($key);
    }

    /**
     * 获取指定哈希的所有属性和值
     *
     * @param  string $key
     * @return array
     */
    public function hashGetAll($key)
    {
        return $this->client->hGetAll($key);
    }

    /**
     * 增加指定哈希指定属性的值并返回结果
     *
     * @param  string $key
     * @param  string $prop
     * @param  int $step
     * @return int
     */
    public function hashIncr($key, $prop, $step = 1)
    {
        return $this->client->hIncrBy($key, $prop, $step);
    }

    /**
     * 增加或更新排序集合成员
     *
     * @param  string $key
     * @param  number $score
     * @param  string $member
     * @return int
     */
    public function zsetAdd($key, $score, $member)
    {
        return $this->client->zAdd($key, $score, $member);
    }

    /**
     * 增加排序集合指定成员的分数
     *
     * @param  string $key
     * @param  string $member
     * @param  int $score
     * @return float
     */
    public function zsetIncr($key, $member, $score = 1)
    {
        return $this->client->zIncrBy($key, $score, $member);
    }

    /**
     * 删除排序集合指定成员
     *
     * @param  string $key
     * @param  string $member
     * @return int
     */
    public function zsetDel($key, $member)
    {
        return $this->client->zRem($key, $member);
    }

    /**
     * 删除指定分数范围中的集合袁术
     *
     * @param string $key
     * @param int $start
     * @param int $end
     * @return int
     */
    public function zRemRangeByScore($key, $start, $end)
    {
    	return $this->client->zRemRangeByScore($key, $start, $end);
    }

    /**
     * 获取排序集合成员数量
     *
     * @param  string $key
     * @return int
     */
    public function zsetLen($key)
    {
        return $this->client->zCard($key);
    }

    /**
     * 获取按分数排序后的排序集合成员信息
     *
     * @param  string $key
     * @param  int $start
     * @param  int $end
     * @param  bool $withScores
     * @param  bool $desc
     * @return array(member)|array(member:score)
     */
    public function zsetRange($key, $start, $end, $withScores = false, $desc = true)
    {
        return $desc
            ? $this->client->zRevRange($key, $start, $end, $withScores)
            : $this->client->zRange($key, $start, $end, $withScores);
    }

    /**
     * 获取排序集合指定分数范围的成员信息
     *
     * @param  string $key
     * @param  float $minScore
     * @param  float $maxScore
     * @param  bool $withScores
     * @param  bool $desc
     * @return array(member)|array(member:score)
     */
    public function zsetRangeByScore($key, $minScore, $maxScore, $withScores = false, $desc = true)
    {
        return $desc
            ? $this->client->zRevRangeByScore($key, $minScore, $maxScore, array("withscores" => $withScores))
            : $this->client->zRangeByScore($key, $minScore, $maxScore, array("withscores" => $withScores));
    }

    /**
     * 获取排序集合指定成员的分数
     *
     * @param  string $key
     * @param  string $member
     * @return float
     */
    public function zsetScore($key, $member)
    {
        return $this->client->zScore($key, $member);
    }

    /**
     * 排序集合：取指定成员的排名
     *
     * @param  string $key
     * @param  string $member
     * @param  bool $desc
     * @return int
     */
    public function zsetRank($key, $member, $desc = true)
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        $rank = $desc ? $this->client->zRevRank($key, $member) : $this->client->zRank($key, $member);
        return ($rank === false ? 0 : $rank + 1);
    }

    /**
     * 按权重联合排序多个排序集合并存入目标集合
     *
     * @param  string $destSet
     * @param  array $sourceSets
     * @param  array $weights
     * @return int
     */
    public function zsetUnion($destSet, $sourceSets, $weights)
    {
        return $this->client->zUnion($destSet, $sourceSets, $weights);
    }

    /**
     * 增加集合成员
     *
     * @param  string $key
     * @param  mixed $member
     * @return int
     */
    public function setAdd($key, $member)
    {
        return $this->client->sAdd($key, $member);
    }

    /**
     * 删除指定集合成员
     *
     * @param  string $key
     * @param  mixed $member
     * @return int
     */
    public function setDel($key, $member)
    {
        return $this->client->sRem($key, $member);
    }

    /**
     * 获取指定集合的成员数量
     *
     * @param  string $key
     * @return int
     */
    public function setLen($key)
    {
        return $this->client->sCard($key);
    }

    /**
     * 弹出集合的最后一个元素
     *
     * @param  string $key
     * @return string|bool
     */
    public function setPop($key)
    {
        return $this->client->sPop($key);
    }

    /**
     * 获取指定集合的随机成员
     *
     * @param  string $key
     * @return string|bool
     */
    public function setRand($key)
    {
        return $this->client->sRandMember($key);
    }

    /**
     * 获取指定集合所有成员
     *
     * @param  string $key
     * @return array
     */
    public function setMembers($key)
    {
        return $this->client->sMembers($key);
    }

    /**
     * 判断成员是否在指定集合内
     *
     * @param  string $key
     * @param  mixed $member
     * @return bool
     */
    public function setIsMember($key, $member)
    {
        return $this->client->sIsMember($key, $member);
    }

    /**
     * 获取列表中指定索引的值
     *
     * @param  string $key
     * @param  int $index
     * @return bool|string
     */
    public function listGet($key, $index)
    {
        return $this->client->lIndex($key, $index);
    }

    /**
     * 设置列表中指定索引的值
     *
     * @param  string $key
     * @param  int $index
     * @param  string $value
     * @return bool
     */
    public function listSet($key, $index, $value)
    {
        return $this->client->lSet($key, $index, $value);
    }

    /**
     * 将值压入列表头部
     *
     * @param  string $key
     * @param  string $value
     * @return int
     */
    public function listShift($key, $value)
    {
        return $this->client->lPush($key, $value);
    }

    /**
     * 将值压入列表尾部
     *
     * @param  string $key
     * @param  string $value
     * @return int
     */
    public function listPush($key, $value)
    {
        return $this->client->rPush($key, $value);
    }

    /**
     * 将列表头部的值弹出
     *
     * @param  string $key
     * @return bool|string
     */
    public function listUnshift($key)
    {
        return $this->client->lPop($key);
    }

    /**
     * 将列表尾部的值弹出
     *
     * @param  string $key
     * @return bool|string
     */
    public function listPop($key)
    {
        return $this->client->rPop($key);
    }

    /**
     * 获取列表长度
     *
     * @param  string $key
     * @return int
     */
    public function listLen($key)
    {
        return $this->client->lLen($key);
    }

    /**
     * 截取并存储列表
     *
     * @param  string $key
     * @param  int $start
     * @param  int $end
     * @return bool
     */
    public function listTrim($key, $start, $end)
    {
        return $this->client->lTrim($key, $start, $end);
    }

    /**
     * 获取列表的一段
     *
     * @param  string $key
     * @param  int $start
     * @param  int $end
     * @return array
     */
    public function listRange($key, $start, $end)
    {
        return $this->client->lRange($key, $start, $end);
    }

    /**
     * 清空当前数据库
     *
     * @return bool
     */
    public function flush()
    {
        return $this->client->flushDB();
    }

    /**
     * 清空所有数据库
     *
     * @return bool
     */
    public function flushAll()
    {
        return $this->client->flushAll();
    }

    /**
     * 输出服务器统计信息
     *
     */
    public function stat()
    {
        $this->client->info();
    }

    /**
     * 获取指定键名的剩余时间
     *
     * @param  string $key
     * @return mixed
     */
    public function ttl($key)
    {
        return $this->client->ttl($key);
    }
}
