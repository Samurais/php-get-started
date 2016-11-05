<?php
namespace framework\config;

/**
 * hash数据库配置信息
 * 
 * @package framework\config
 */
class HashDBConfiguration
{
	/**
	 * 数据库dsn链接
	 * 
	 * @var String
	 */
	public $dsn;
	/**
	 * 数据库用户名
	 * 
	 * @var String
	 */
	public $user;
	/**
	 * 数据库密码
	 * 
	 * @var String
	 */
	public $pass;
	/**
	 * hash数据库前缀
	 * 
	 * @var string
	 */
	public $dbprefix;
	/**
	 * 默认编码
	 * 
	 * @var String
	 */
	public $charset;
	/**
	 * 索引值
	 * 
	 * @var int
	 */
	public $rate;
	
	/**
	 * 构造函数
	 *
	 * @param int $rate
	 * @param string $dsn
	 * @param string $user
	 * @param string $pass
	 * @param string $dbprefix
	 * @param string $charset
	 */
	public function __construct($rate, $dsn, $user, $pass, $dbprefix, $charset)
	{
		$this->dsn = $dsn;
		$this->user = $user;
		$this->pass = $pass;
		$this->dbprefix = $dbprefix;
		$this->charset = $charset;
		$this->rate = $rate;
	}
}