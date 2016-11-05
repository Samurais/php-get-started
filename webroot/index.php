<?php
use common\Utils;
use framework\core\Context;
use framework\dispatcher\HTTPRequestDispatcher;

define('ROOT_PATH', realpath('..'));

include_once("../lib/framework/setup.php");

Context::setRootPath(realpath('..'));

Context::initialize();  //加载inf相关目录下所有文件

Utils::initConfig();    //加载配置

$dispatcher = new HTTPRequestDispatcher();
$dispatcher->dispatch();
