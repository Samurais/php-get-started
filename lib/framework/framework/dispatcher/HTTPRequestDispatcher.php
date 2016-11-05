<?php // -*-coding:utf-8; mode:php-mode;-*-
	  
namespace framework\dispatcher;

/**
 * HTTP请求转发器，IRequestDispacher的一个实现，用于分发HTTP的请求。
 * 当GET或者POST信息包含类似于act=CtrlName.methodName时，将执行CtrlName类的methodName方法。
 * @author xodger@gmail.com
 * @package framework\dispatcher
 */
class HTTPRequestDispatcher extends RequestDispatcherBase{

	private $ctrlClassName;

	private $ctrlMethodName;

	public function __construct()
	{		
		$this->defaultAction = 'Index.main';
			
		if(array_key_exists("act", $_REQUEST))
        {
        	$act = $_REQUEST['act'];
        }
        else
        {
        	$act = $this->defaultAction;
        }

        if (preg_match ( '/^([a-z_]+)\.([a-z_]+)$/i', $act, $arr ))
        {
        	$this->ctrlClassName = ucfirst($arr[1]).'Ctrl';
        	$this->ctrlMethodName = $arr[2];
        }
	}
	
	public function getCtrlClassName()
	{
		return $this->ctrlClassName;
	}
	
	public function getCtrlMethodName()
	{
		return $this->ctrlMethodName;
	}
}