<?php // -*-coding:utf-8; mode:php-mode;-*-
use framework\util;
initFrameworkPath();

use framework\core\Context;
use framework\util\Formater;
use framework\view\JSONView;

require_once FRAMEWORK_PATH . DIRECTORY_SEPARATOR . "framework/core/Context.php";

set_exception_handler("exception_handler");

function __autoload($class) 
{
	$baseClasspath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
	$classpath = Context::getClassesRoot(). DIRECTORY_SEPARATOR . $baseClasspath;

	if (!\is_file($classpath))
	{
		$classpath = FRAMEWORK_PATH . DIRECTORY_SEPARATOR . $baseClasspath;
	}
	
	if (!\is_file($classpath))
	{
		$baseClasspath = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
		$classpath = ROOT_PATH . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "pheanstalk" . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . $baseClasspath;
	}

	if (\is_file($classpath))
	{
		require_once($classpath);
        if (\defined("LOG_QUERY")) \common\Log::info("load_class",array("classpath"=>$classpath));
	}
}

function initFrameworkPath()
{
	if (!defined('FRAMEWORK_PATH'))
	{		
		define('FRAMEWORK_PATH', __DIR__.DIRECTORY_SEPARATOR);
	}
}

function exception_handler($exception) 
{	
	if ($exception instanceof common\GameException)
	{
		$exception->exceptionHandler($exception);
	}
	else
	{
		$exceptionView = new JSONView(Formater::formatException($exception));
		$exceptionView->display();
	}
}


