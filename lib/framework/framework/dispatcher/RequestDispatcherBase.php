<?php // -*-coding:utf-8; mode:php-mode;-*-

namespace framework\dispatcher;

use framework\core\IView;
use framework\core\IRequestDispatcher;
use framework\core\IController;
use framework\core\Context;
use framework\view\AMFView;

/**
 * IRequestDispatcher的抽象实现，它实现了dispatch方法，并且定义了getCtrlClassName和getCtrlMethodName两个抽象方法，其子类只需实现这两个方法即可。
 * @author xodger@gmail.com
 * @package framework\dispatcher
 */
abstract class RequestDispatcherBase implements IRequestDispatcher {

	/**
	 * 默认动作
	 * 
	 * @var string
	 */
	protected $defaultAction;
	
	public function dispatch()
	{		
		$ctrlClass = Context::getCtrlNamespace() . "\\" . $this->getCtrlClassName();
		$ctrlMethod = $this->getCtrlMethodName();

        $ctrl = new $ctrlClass();
		$filtered = false;
		
		if ($ctrl instanceof IController)
		{
			try {
				$ctrl->setDispatcher($this);			
				$filtered = !$ctrl->beforeFilter();
			}
			catch (\Exception $e)
			{
				exception_handler($e);
				exit;
			}
		}
		
		$exception = null;
		
		$view = null;
		
		if (!$filtered)
		{
			try
			{
				if (method_exists($ctrl, "__call"))
				{
					$view = $ctrl->__call($ctrlMethod, array());
				}
				else 
				{
					$view = $ctrl->$ctrlMethod();
				}
			}
			catch(Exception $e)
			{
				exception_handler($e);
				exit;
			}
		}
		
		if ($ctrl instanceof IController)
		{
			try {
				$ctrl->afterFilter();
			}
			catch (\Exception $e)
			{
				exception_handler($e);
				exit;
			}
		}
		
		if ($exception != null)
		{
			exit;//throw $exception; 原来此处的逻辑是错的，$exception未定义。
		}
		
		if ($view instanceof IView)
		{
			$view->display();
		}
	}
	
	/**
	 * 获取控制器类名
	 * 
	 * @return String
	 */
	abstract public function getCtrlClassName();
	
	/**
	 * 获取控制器方法名
	 * 
	 * @return String
	 */
	abstract public function getCtrlMethodName();
}