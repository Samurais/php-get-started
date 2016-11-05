<?php
namespace framework\util;

use \Exception;

/**
 * 格式转换工具类
 * 
 * @package framework\util
 */
class Formater
{	
	/**
	 * 格式化异常
	 * 
	 * @param Exception $exception
	 * @return array
	 */
	public static function formatException(\Exception $exception) 
	{
		$exceptionHash = array(
	        'className'	=> 'Exception',
	        'message'	=> $exception->getMessage(),
			'code'		=> $exception->getCode(),
		);
		
		if (DEBUG_MODE)
		{
			$exceptionHash['file'] = $exception->getFile();
			$exceptionHash['line'] = $exception->getLine();
			$exceptionHash['trace'] = array();
			
			$traceItems = $exception->getTrace();
			
			foreach ($traceItems as $traceItem) 
			{
				$traceHash = array(
					'file' => $traceItem['file'],
					'line' => $traceItem['line'],
					'function' => $traceItem['function'],
					'args' => array()
				);
				
				if (!empty($traceItem['class']))
				{
					$traceHash['class'] = $traceItem['class'];
				}
				
				if (!empty($traceItem['type']))
				{
					$traceHash['type'] = $traceItem['type'];
				}
				
				if (!empty($traceItem['args'])) 
				{
					foreach ($traceItem['args'] as $argsItem) 
					{
						$traceHash['args'][] = var_export($argsItem, true);
					}
				}			
				
				$exceptionHash['trace'][] = $traceHash;
			}
		}
		
		return $exceptionHash;
	}
}