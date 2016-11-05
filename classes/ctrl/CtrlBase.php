<?php
namespace ctrl;

use framework\core\IController;
use framework\core\IRequestDispatcher;

use framework\dispatcher\AMFRequestDispatcher;
use framework\dispatcher\HTTPRequestDispatcher;
use framework\dispatcher\ShellRequestDispatcher;

use framework\view\JSONView;
use framework\view\StringView;

/** 
 * 控制器基类
 * 
 */
class CtrlBase implements IController
{
	/**
	 * 请求分发器
	 * 
	 * @var IRequestDispatcher
	 */
	protected $dispatcher;
	
	/**
	 * 传入参数
	 *
	 * @var array
	 */
	protected $params = array();	
	
	/**
	 * 设置请求分发器
	 * 
	 * @param IRequestDispatcher $dispatcher
	 */
	public function setDispatcher(IRequestDispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
		
		if ($this->dispatcher instanceof HTTPRequestDispatcher)
		{
			$this->params = empty($_REQUEST) ? array() : $_REQUEST;
		}
		elseif ($this->dispatcher instanceof ShellRequestDispatcher)
		{
			$this->params = empty($_SERVER['argv']) ? array() : $_SERVER['argv'];
		}
	}
	
	/**
	 * 前置过滤器
	 * 
	 * @return bool
	 */
	public function beforeFilter()
	{
		return true;
	}
	
	/**
	 * 结束过滤器
	 * 
	 */
	public function afterFilter()
	{
	}
    
    
    /**
     * 获取整数参数
     * 
     * @param array $params
     * @param string $key
     * @param bool $abs
     * @param bool $notEmpty
     * @return int
     */
	protected function getInteger($params, $key, $abs=false, $notEmpty=false)
    {
    	$params = (array)$params;
    	$integer = array_key_exists($key, $params) ? intval($params[$key]) : 0;
    	
    	if ($abs)
    	{
    		$integer = abs($integer);
    	}
    	
    	if (!empty($notEmpty) && empty($integer))
    	{
    		throw new common\GameException('1002parameterError');
    	}
    	
    	return $integer;
    }
    
	/**
     * 获取整数数组参数
     * 
     * @param array $params
     * @param string $key
     * @param bool $abs
     * @param bool $notEmpty
     * @return array
     */
	protected function getIntegers($params, $key, $abs=false, $notEmpty=false)
    {
    	$params = (array)$params;
    	$integers = (array_key_exists($key, $params) && !empty($params[$key])) ? array_map('intval', (array)$params[$key]) : array();       
    	
    	if ($abs)
    	{
    		$integers = array_map('abs', $integers);
    	}
    	
    	if (!empty($notEmpty) && empty($integers))
    	{
    		throw new common\GameException('1002parameterError');
    	}
    	
    	return $integers;
    }
    
    /**
     * 获取浮点数参数
     * 
     * @param array $params
     * @param string $key
     * @param bool $abs
     * @param bool $notEmpty
     * @return float
     */
	protected function getFloat($params, $key, $abs=false, $notEmpty=false)
    {
    	$params = (array)$params;
    	$float = array_key_exists($key, $params) ? floatval($params[$key]) : 0;  
    	     
    	if ($abs)
    	{
    		$float = abs($float);
    	}

    	if (!empty($notEmpty) && empty($float))
    	{
    		throw new common\GameException('1002parameterError');
    	}
    	
    	return $float;
    }
    
    /**
     * 获取字符串参数
     * 
     * @param array $params
     * @param string $key
     * @param bool $notEmpty
     * @param bool $trim 是否trim
     * 
     * @return string
     */
	protected function getString($params, $key, $notEmpty=false, $trim = true)
    {
    	$params = (array)$params;
    	$string = array_key_exists($key, $params) ? ($trim ? trim($params[$key]) : $params[$key]) : '';
    	
    	if (!empty($notEmpty) && strlen($string) == 0)
    	{
    		throw new common\GameException('1002parameterError');
    	}
    	  	
    	return $string;
    }
    
	/**
     * 获取字符串数组参数
     * 
     * @param array $params
     * @param string $key
     * @param bool $notEmpty
     * @return array
     */
	protected function getStrings($params, $key, $notEmpty=false)
    {
    	$params = (array)$params;
    	$strings = (array_key_exists($key, $params) && !empty($params[$key])) ? array_map('trim', (array)$params[$key]) : array();       
    	
    	if (!empty($notEmpty) && empty($strings))
    	{
    		throw new common\GameException('1002parameterError');
    	}
    	
    	return $strings;
    }
}
