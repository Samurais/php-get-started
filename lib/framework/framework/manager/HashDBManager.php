<?php 
namespace framework\manager;

use framework\helper;

use framework\config;

use \PDO;
use framework\config\HashDBConfiguration;
use framework\helper\PDOHelper;

/**
 * PDOHelper管理工具，用于管理PDOHelper对象的工具。
 * 
 * @package framework\manager
 */
class HashDBManager
{
	/**
	 * HashDB配置
	 * 
	 * @var <HashDBConfiguration>array
	 */
	private static $configs;
	/**
	 * PDOHelper实例
	 * 
	 * @var <PDOHelper>array
	 */
	private static $instances;
	
	/**
	 * hash基数
	 * 
	 * @var int
	 */
	private static $hashBase;
	
	/**
	 * 设置hash基数
	 * 
	 * @param int $hashBase
	 */
	public static function setHashBase($hashBase)
	{
		self::$hashBase = $hashBase;
	}
	
	/**
	 * 添加PDO配置
	 * 
	 * @param HashDBConfiguration $config
	 */
	public static function addConfigration(HashDBConfiguration $config)
	{
		self::$configs[$config->rate] = $config;
	}
	
	/**
	 * 获取信息散列后对应的数据库索引
	 * 
	 * @param string $userId
	 * @return int
	 */
	public static function getHashIndex($userId)
	{
		$hashIndex = sprintf('%u', crc32($userId) >> 16 & 0xffff) % self::$hashBase;
		return $hashIndex;
	}
	
	/**
	 * 根据hash索引获取对应DB索引
	 * 
	 * @param int $hashIndex
	 * 
	 * @return int
	 */
	public static function getDBIndex($hashIndex)
	{
		krsort(self::$configs);
		$dbIndexs = array_keys(self::$configs);
		
		foreach ($dbIndexs as $dbIndex)
		{
			if ($hashIndex >= $dbIndex)
			{
				return $dbIndex;
			}
		}
		
		return null;
	}
	
	/**
	 * 获取PDOHelper实例
	 * 
	 * @param string $dbIndex
	 * @return \PDO
	 */
	public static function getInstance($dbIndex)
	{
		if (empty(self::$instances[$dbIndex]))
		{	
			$config = self::$configs[$dbIndex];
			$pdo = new \PDO($config->dsn, $config->user, $config->pass, array(
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$config->charset}';",
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			));
			self::$instances[$dbIndex] = $pdo;
		}
		
		return self::$instances[$dbIndex];
	}
	
	/**
	 * 获取PDOHelper实例
	 * 
	 * @param string $class
	 * @param string $userId
	 * 
	 * @return PDOHelper
	 */
	public static function getHashDBHelper($class,$dbIndex)
	{

		// $dbIndex = self::getDBIndex($hashIndex);
		
		$pdo = self::getInstance($dbIndex);

		$PDOHelper = new helper\PDOHelper($class);
		$PDOHelper->setPdo($pdo);
		$PDOHelper->setDBName($dbIndex);
		
		return $PDOHelper;
	}
	
	/**
	 * 获取PDOHelper实例
	 * 
	 * @param string $class
	 * @param string $userId
	 * 
	 * @return PDOHelper
	 */
	public static function getHelper($class, $userId)
	{
		$hashIndex = self::getHashIndex($userId);
		$dbIndex = self::getDBIndex($hashIndex);
		
		$pdo = self::getInstance($dbIndex);
		$config = self::$configs[$dbIndex];
		
		$PDOHelper = new helper\PDOHelper($class);
		$PDOHelper->setPdo($pdo);
		$PDOHelper->setDBName($config->dbprefix . $hashIndex);
		
		return $PDOHelper;
	}
}