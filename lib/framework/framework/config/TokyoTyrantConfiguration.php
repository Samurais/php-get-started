<?php
namespace framework\config;

/**
 * TokyoTyrant配置信息
 * 
 * @author zivn
 * @package framework\config
 */
class TokyoTyrantConfiguration
{
	/**
	 * TokyoTyrant服务器dsn链接
	 * 
	 * @var string
	 */
	public $uri;
	
	/**
	 * 构造函数
	 * 
	 * @param string $uri
	 */
	public function __construct($uri)
	{
		$this->uri = $uri;
	}
}