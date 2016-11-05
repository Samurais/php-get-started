<?php
namespace common;

use framework\core\Context;
use framework\view;
use framework\config;
use framework\manager;
use framework\helper;

class Utils
{
	/**
	 * 被操作锁定的键名
	 *
	 * @var string
	 */
	private static $operateLockKey;
	/**
	 * 开始运行时间
	 *
	 * @var int
	 */
	private static $startTime;
	/**
	 * 执行时间戳
	 *
	 * @var int
	 */
	private static $executeTime = NOW_TIME;
	
	/**
	 * 分服ID
	 * 
	 * @var int
	 */
	private static $serverId = 1;
	
	/*
	 * 批量方法之间的分隔符号
	 */
	public static $actionDelimiter = '#';
	
	/**
	 * 登录信息
	 * 
	 * @var array
	 */
	private static $loginInfo = array();

    /**
     * 执行递归时候的最大次数
     */
    private static $max_recursive_count = 1000;

    /**
     * 统计递归的次数
     */
    private static $recursive_offset = 0;


    private static $country_list = array
    (
        "中国" => 0,
        "美国" => 1,
        "加拿大" => 2,
        "英国" => 3,
        "澳大利亚" => 4,
        "新加坡" => 5,
        "香港" => 6,
        "爱尔兰" => 7,
        "以色列" => 8,
        "牙买加" => 9,
        "新西兰" => 10,
        "南非" => 11,
#英联邦
        "孟加拉国" => 12,
        "博茨瓦纳" => 12,
        "喀麦隆" => 12,
        "加纳" => 12,
        "印度" => 12,
        "肯尼亚" => 12,
        "莱索托" => 12,
        "马来西亚" => 12,
        "马拉维" => 12,
        "莫桑比克" => 12,
        "纳米比亚" => 12,
        "尼日利亚" => 12,
        "巴基斯坦" => 12,
        "巴布亚新几内亚" => 12,
        "卢旺达" => 12,
        "塞拉利昂" => 12,
        "斯里兰卡" => 12,
        "坦桑尼亚" => 12,
        "乌干达" => 12,
        "赞比亚" => 12,
        "安提瓜和巴布达" => 12,
        "巴哈马" => 12,
        "巴巴多斯" => 12,
        "伯利兹" => 12,
        "文莱" => 12,
        "塞浦路斯" => 12,
        "多米尼克" => 12,
        "斐济" => 12,
        "斐济群岛" => 12,
        "格林纳达" => 12,
        "圭亚那" => 12,
        "基里巴斯" => 12,
        "马尔代夫" => 12,
        "马耳他" => 12,
        "毛里求斯" => 12,
        "瑙鲁" => 12,
        "美属萨摩亚" => 12,
        "塞舌尔" => 12,
        "所罗门群岛" => 12,
        "汤加" => 12,
        "特立尼达和多巴哥" => 12,
        "图瓦卢" => 12,
        "瓦努阿图" => 12,
#欧盟
        "奥地利" => 13,
        "比利时" => 13,
        "保加利亚" => 13,
        "克罗地亚" => 13,
        "捷克" => 13,
        "丹麦" => 13,
        "爱沙尼亚" => 13,
        "芬兰" => 13,
        "法国" => 13,
        "德国" => 13,
        "希腊" => 13,
        "匈牙利" => 13,
        "意大利" => 13,
        "拉脱维亚" => 13,
        "立陶宛" => 13,
        "卢森堡" => 13,
        "荷兰" => 13,
        "波兰" => 13,
        "葡萄牙" => 13,
        "罗纳尼亚" => 13,
        "斯洛伐克" => 13,
        "斯洛文尼亚" => 13,
        "西班牙" => 13,
        "瑞典" => 13,
    );


    private static  $zone_list = array(
        '0' => array(),
        '1' => array('Asia/Shanghai', '东八区'),
        '2' => array('America/New_York', 'EST'),
        '3' => array('America/Chicago', 'CST'),
        '4' => array('America/Denver', 'MST'),
        '5' => array('America/Los_Angeles', 'PST'),
        '6' => array('Europe/London', 'BST'),
        '7' => array('Asia/Tokyo', '东九区'),
        '8' => array('Asia/Seoul', '东九区'),
    );

	//时区对应关系
	private static $GMTzomeCpList = array(
		'+8:00'=>array(1,'Asia/Shanghai'),
		'-05:00'=>array(2,'America/New_York'),
		'-06:00'=>array(3,'America/Chicago'),
		'-07:00'=>array(4,'America/Denver'),
		'-08:00'=>array(5,'America/Los_Angeles'),
		'+00:00'=>array(6,'Europe/London'),
	);

    /**
	 * 获取执行时间
	 *
	 * @return int
	 */
	public static function getExecuteTime()
	{
		return self::$executeTime;
	}

	/**
	 * 获取执行日期
	 *
	 * @param boolean $fullFormat
	 * @return datetime
	 */
	public static function getExecuteDate($fullFormat=true)
	{
		return date(empty($fullFormat) ? 'Y-m-d' : 'Y-m-d H:i:s', self::$executeTime);
	}

	/**
	 * 设置执行时间
	 *
	 * @param int $executeTime
	 */
	public static function setExecuteTime($executeTime)
	{
		self::$executeTime = $executeTime;
	}

	/**
	 * 合并路径
	 *
	 * @param ...string
	 * @return string
	 */
	public static function mergePath()
	{
		return implode(DIRECTORY_SEPARATOR, func_get_args());
	}

	/**
	 * 从数组中获取一个随机成员
	 *
	 * @param array $items
	 * @return mixed
	 */
	public static function getRandomItem($items)
	{
		return $items[array_rand($items)];
	}
	
	/**
	 * 从数组中随机取多个成员
	 * 
	 * @param array $items
	 * @param int $limit
	 * @return array
	 */
	public static function getRandomItems($items,$limit = 1)
	{
		shuffle($items);
		
		if($limit > 0 and $limit <= count($items))
		{
			return array_slice($items,0,$limit);
		}
		
		return $items;
	}

	/**
	 * 按权重获取类型
	 *
	 * @param array $weights [type => weight]
	 * @return int
	 */
	public static function getWeightItem($weights)
	{
    	$randValue = mt_rand(1, array_sum($weights));
        $limitValue = 0;

        foreach ($weights as $type => $weight)
        {
        	$limitValue += $weight;

        	if ($randValue <= $limitValue)
        	{
        		return $type;
        	}
        }

        return 0;
	}	

	/**
	 * 今天是否更新过
	 * 
	 * @return boolean
	 */
	public static function dayUpdated($lastModify)
	{
		$nowTime = self::getExecuteTime();
		$limitTime = strtotime(date('Y-m-d 03:00:00', $nowTime));
		
		if ($nowTime < $limitTime)
		{
			$limitTime -= 86400;
		}
		
		return ($lastModify < $limitTime) ? false : true;
	}
	
	/**
	 * 将秒转换为时间
	 *
	 * @param int $seconds
	 * @return string
	 */
	public static function secondToTime($seconds)
	{
		return sprintf('%02d:%02d:%02d', intval($seconds / 3600), intval(($seconds % 3600) / 60), $seconds % 60);
	}

	/**
	 * 是否是原生AMF请求
	 *
	 * @return boolean
	 */
	public static function isNativeAMFRequest()
	{
		
		//if (!empty($_SERVER['CONTENT_TYPE']) && ($_SERVER['CONTENT_TYPE'] == "application/octet-stream" || $_SERVER['CONTENT_TYPE'] == "application/x-amf"))
		if(!empty($_REQUEST['isAMF']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	
	
	/**
	 * 是否是AMF请求
	 *
	 * @return boolean
	 */
	public static function isAMFRequest()
	{
		if (self::isNativeAMFRequest() || !empty($_REQUEST['isAMF']))
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	/**
	 * 是否是msgpack请求
	 *
	 * @return boolean
	 */
	public static function isMSGPackRequest()
	{
		if (!empty($_REQUEST['isMSGPack']))
		{
			return true;
		}
		else
		{
			return false;
		}
	
	}
	
	/**
	 * 是否是json请求
	 *
	 * @return boolean
	 */
	public static function isJSONRequest()
	{
		if (!empty($_REQUEST['isJSON']))
		{
			return true;
		}
		else
		{
			return false;
		}
	
	}
	
	public static function sendMobileMessage($mobileArray,$message,$serverMark)
	{
		require_once ($GLOBALS ['KBV_CFG'] ['LIBPATH'] . DIRECTORY_SEPARATOR . 'reg' . DIRECTORY_SEPARATOR . 'passport_api_client.php');
		$APIClient = new \PassportAPIClient ( $GLOBALS ['KBV_CFG'] ['klconfig'] ['API_TOKEN'], $GLOBALS ['KBV_CFG'] ['klconfig'] ['API_SERVER_ADDR'] );
		foreach($mobileArray as $phone)
		{
			$APIClient->system_sendSms($phone,$message);
		}
		return true;
	}

    //apns ios推送
	public static function iosPush($tokenArr,$msg)
	{
	    require_once($GLOBALS ['KBV_CFG'] ['LIBPATH'] . DIRECTORY_SEPARATOR .'ApnsPHP/Autoload.php');
        $push = new ApnsPHP_Push(ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,'server_certificates_bundle_sandbox.pem');
        $push->setRootCertificationAuthority('entrust_root_certification_authority.pem');
        $push->connect();
        foreach($tokenArr as $value)
        {
            if(!empty($value['token']))
            {
                $message = new ApnsPHP_Message($value['token']);
                $message->setText(!empty($value['mes'])?$value['mes']:$msg);
                $push->add($message);
            }
        }
        $push->send();
        $push->disconnect();
	}

    //gcm google云推送
	public static function androidPush($tokenArr,$msg)
	{

        foreach($tokenArr as $value)
		{
			$result['registration_ids'][] = trim($value['token']);

		}
		$result['data'] ['message'] = $msg;
		$url = "http://android.googleapis.com/gcm/send";
		$ch = curl_init();
		curl_setopt_array ( $ch, array ( CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($result), CURLOPT_HTTPHEADER=>array("Content-Type:application/json", "Authorization:key=".$GLOBALS['googleKey']),  CURLOPT_SSL_VERIFYHOST =>false,CURLOPT_SSL_VERIFYPEER => false,CURLOPT_CONNECTTIMEOUT => 2, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6,CURLOPT_URL=>$url));
        curl_exec($ch);
	}

	private static function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = self::object_to_array($value);
            }
            return $result;
        }
        return $data;
    }

	/**
	 * 初始化配置
     * @todo 对上传的数据校验
	 */
	public static function initConfig()
	{
		self::$startTime = self::getMicroTime();

        self::$recursive_offset = 0; // 初始化递归次数

		// 调试模式设置
		ini_set('display_errors', DEBUG_MODE ? 'On' : 'Off');

		//设置错误报告级别
		error_reporting(E_ALL ^ E_NOTICE);

		// 设置异常回调
//		set_exception_handler('common\GameException::exceptionHandler');

		if (self::isNativeAMFRequest())
		{
			$stream = file_get_contents('php://input');
			$requestInfo = DataView::AMFDecode($stream);

			if (!empty($requestInfo) )
			{
				$_REQUEST['key'] = self::object_to_array($requestInfo);
                //$_REQUEST['key'] = self::filter($_REQUEST['key']);
				
			}
			self::$actionDelimiter = '#';
		}
		
		elseif(self::isJSONRequest()) {
			$stream = file_get_contents('php://input');
			$requestInfo = json_decode($stream);
			if(!empty($resuestInfo))
			{
                $_REQUEST['key'] = self::object_to_array($requestInfo);
			}
			else 
			{
                $_REQUEST['key'] = self::object_to_array(json_decode($_REQUEST['key'],1));
			}
            //$_REQUEST['key'] = self::filter($_REQUEST['key']);
			self::$actionDelimiter = '|';
		}
		// 初始化redis
		// foreach($GLOBALS['redis_indexes'] as $v)
		// {
		//     $redisConfig = new config\RedisConfiguration(REDIS_HOST,REDIS_PORT,$v);
		//     manager\RedisManager::addConfig($v,$redisConfig);
        // }
        
		// 初始化公共数据库配置
		$pdoConfig = new config\PDOConfiguration(
			COMMON_DB_HOST,
			COMMON_DB_USER,
			COMMON_DB_PASS,
			DEFAULT_CHARSET
		);
		manager\PDOManager::addConfigration(CONFIG_COMMON_DB_PREFIX, $pdoConfig);
		
		// 初始化用户数据库配置
		$pdoConfig = new config\PDOConfiguration(
			USER_DB_HOST,
			USER_DB_USER,
			USER_DB_PASS,
			DEFAULT_CHARSET
		);
		manager\PDOManager::addConfigration(CONFIG_USER_DB_PREFIX, $pdoConfig);




		// 初始化散列数据库配置
		if (defined('HASH_DBN_SIZE') && HASH_DBN_SIZE && isset($GLOBALS['HASH_DB']) && !empty($GLOBALS['HASH_DB']))
		{
			manager\HashDBManager::setHashBase(HASH_DBN_SIZE);
			
			foreach ($GLOBALS['HASH_DB'] as $hashdb)
			{
				$hashDBConfiguration = new config\HashDBConfiguration(
					$hashdb['rate'],
					$hashdb['dsn'],
					$hashdb['user'],
					$hashdb['pass'],
					$hashdb['dbprefix'],
					DEFAULT_CHARSET
				);
				manager\HashDBManager::addConfigration($hashDBConfiguration);
			}
		}
	}

    /**
     * 对参数进行过滤
     * @param $array
     * @return array
     */
    public static function filter($data)
    {
        // 判断是否大于递归次数，如果大于，则直接返回数据
        if(self::$recursive_offset >= self::$max_recursive_count)
        {
            return $data;
        }

        if(is_array($data))
        {
            self::$recursive_offset++; # 累积递归次数

            $ret = array();
            foreach($data as $key => $val)
            {
                $ret[$key] = self::filter($val);
            }
            return $ret;
        }
        elseif(is_numeric($data))
        {
            return (int)$data;
        }
        elseif(is_null($data) || ($data =='') || is_bool($data))
        {
            return $data;
        }
        else
        {
            return htmlspecialchars(stripcslashes($data));
        }
    }

	/**
	 * 打印出变量信息
	 * 
	 * @param mixed $value
	 */
	public static function debug($value)
	{
		echo "<pre>";
		print_r($value);
		echo "</pre>";
	}
	
    /**
     * 获取客户端IP
     *
     * @return string
     */
    public static function getClientIP()
    {
    	if (defined("APP_API") && strtolower(APP_API) == "qq" && isset($_SERVER['HTTP_QVIA']))
    	{
    		$qvia = $_SERVER['HTTP_QVIA'];
    		$realip = hexdec(substr($qvia, 0, 2)) . "." . hexdec(substr($qvia, 2, 2)) . "." . hexdec(substr($qvia, 4, 2)) . "." . hexdec(substr($qvia, 6, 2));
    	}
    	else 
    	{
			if (isset($_SERVER) && isset($_SERVER["REMOTE_ADDR"]))
			{
				$realip = $_SERVER["REMOTE_ADDR"];
			}else{
				$realip = getenv("REMOTE_ADDR");
			}
    	}
		return addslashes($realip);
    }

	/**
	 * @param $ip
	 * @return bool  true on china
	 */
	public static function  login_in_china( $ip = '' )
	{
        if($ip == '')
        {
            $ip = Utils::getClientIP();
        }
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=".$ip);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		$json_string = curl_exec($curl);
		curl_close($curl);

		$ret = json_decode($json_string, true);
		$countryStr = $ret['country'];

		if(!isset( $countryStr ))
		{
			return false;
		}

		if ( $countryStr == "中国" )  //IP为中国，但是国籍选择不是中国，说明是异地登陆的活着冒充美国用户。
		{
			return true;
		}

		return false;
	}
	
    /**
     * 获取精确的时间
     *
     * @return float
     */
	public static function getMicroTime()
	{
	    list($usec, $sec) = explode(" ", microtime());
	    return ((float)$usec + (float)$sec);
	}

	/**
	 * 返回执行时间
	 *
	 * @return int
	 */
	public static function getRunTime()
	{
		$nowTime = self::getMicroTime();
		$runTime = $nowTime - self::$startTime;
		self::$startTime = $nowTime;
		return $runTime;
	}

    /**
     * 添加用户操作锁定
     *
     * @param string $key
     * @return bool
     */
    public static function operateLock($key)
    {
		$memcachedHelper = new helper\MemcachedHelper(true);

    	for ($i = 0; $i < LOCKER_TERM; $i++)
    	{
    		if ($memcachedHelper->add(LOCKER_PREFIX.$key, true, LOCKER_TERM))
            {
            	self::$operateLockKey = $key;
            	return true;
            }

            sleep(1);
    	}

        return false;
    }

    /**
     * 解除用户操作锁定
     *
     */
    public static function operateUnLock()
    {
    	if (!empty(self::$operateLockKey))
    	{
			$memcachedHelper = new helper\MemcachedHelper(true);
			$memcachedHelper->delete(LOCKER_PREFIX.self::$operateLockKey);
    	}
    }

    /**
     * 获取平台验证字符串
     *
     * @param array $params
     * @return string
     */
    public static function getToken($params)
    {
        if (empty($params) || !is_array($params) || !defined('GAME_GLOBAL_KEY'))
        {
        	return false;
        }

        unset($params['token']);
		unset($params['PHPSESSID']);
		unset($params['act']);

		sort($params, SORT_STRING);
        array_unshift($params, GAME_GLOBAL_KEY);
        return md5(implode('|-|', $params));
    }

    /**
     * 获取登陆信息
     * 
     * @param boolean $forceCookie 默认false
     *
     * @return array
     */
    public static function getLoginInfo($forceCookie = false)
    {
    	if (!$forceCookie && !empty(self::$loginInfo))
    	{
    		return self::$loginInfo;
    	}
    	
    	if (empty($_COOKIE['checkCode']))
    	{
    		return false;
    	}

    	$checkCode = urldecode($_COOKIE['checkCode']);
    	$infoString = base64_decode(substr($checkCode, 15, -25));

    	if (empty($infoString))
    	{
    		return false;
    	}

    	$infoArray = json_decode($infoString);

    	if (empty($infoArray) || !is_array($infoArray) || count($infoArray) != 2)
    	{
    		return false;
    	}

    	$infoHash = substr($checkCode, 0, 15).substr($checkCode, -25);

    	if ($infoHash != sha1(implode('|', $infoArray)))
    	{
    		return false;
    	}

    	$version = array_shift($infoArray);

    	if ($version != CLIENT_VERSION)
    	{
    		return false;
    	}

    	self::$loginInfo = array_combine(array('userId'), array_values($infoArray));
    	
    	return self::$loginInfo;
    }
    
    /**
     * 获取分服ID
     * 
     * @return int
     */
    public static function getServerId()
    {
    	if (defined("USE_FENFU") && USE_FENFU == false)
    	{
    		return 1;
    	}
    	
    	if (!empty(self::$serverId))
    	{
    		return self::$serverId;
    	}
    	
    	if (APP_API == "mobage")
    	{
    		return 1;
    	}
    	
    	if (empty($_COOKIE['checkServer']))
    	{
    		return false;
    	}

    	$checkCode = urldecode($_COOKIE['checkServer']);
    	$infoString = base64_decode(substr($checkCode, 15, -25));

    	if (empty($infoString))
    	{
    		return false;
    	}

    	$infoArray = json_decode($infoString);

    	if (empty($infoArray) || !is_array($infoArray) || count($infoArray) != 2)
    	{
    		return false;
    	}

    	$infoHash = substr($checkCode, 0, 15).substr($checkCode, -25);

    	if ($infoHash != sha1(implode('|', $infoArray)))
    	{
    		return false;
    	}

    	$version = array_shift($infoArray);

    	if ($version != CLIENT_VERSION)
    	{
    		return false;
    	}

    	return array_pop($infoArray);
    }
    
    /**
     * 添加分服key后缀
     * (原始服 1 key不变)
     * 
     * @param string $param 原始参数
     * 
     * @return string
     */
    public static function getKeyParam($param = "", $serverId = null)
    {
    	$serverId = $serverId ? $serverId : self::getServerId();
		if ($serverId != 1)
		{
			return "s" . $serverId . KEY_SEPARATOR . $param;
		}
		return $param;
    }
    
	/**
     * 设置分服ID到Cookie
     * 
     * @param int $serverId
     */
    public static function setServerId($serverId)
    {
    	if (defined("USE_FENFU") && USE_FENFU == false)
    	{
    		self::$serverId = 1;
    		return true;
    	}
    	
    	$infoArray = array(CLIENT_VERSION, $serverId);
    	$infoHash = sha1(implode('|', $infoArray));

    	setcookie('checkServer', substr($infoHash, 0, 15).base64_encode(json_encode($infoArray)).substr($infoHash, 15, 25), time() + 86400, '/', $_SERVER['SERVER_NAME']);
		
    	self::$serverId = $serverId;
    	
    	return true;
    }

    /**
     * 设置用户信息到Cookie
     *
     * @param string $userId
     * @return array
     */
    public static function setLoginInfo($userId)
    {
    	$infoArray = array(CLIENT_VERSION, $userId);
    	$infoHash = sha1(implode('|', $infoArray));

    	setcookie('checkCode', substr($infoHash, 0, 15).base64_encode(json_encode($infoArray)).substr($infoHash, 15, 25), time() + 86400, '/', $_SERVER['SERVER_NAME']);

    	self::$loginInfo = array(
				    		'userId' => $userId
				    	);
    	return self::$loginInfo;
    }
    
    
//	public static function getCountryInfo($code)
//	{
//		//0中国cn 1美国us 2加拿大ca 3 英国  uk 4 澳大利亚 au
//		$info = array();
//		switch ($code) {
//			case 'us';
//				$info['countryId'] = 1;
//				$info['say'] = 1;
//				$info['study'] = 0;
//				break;
//			case 'ca';
//				$info['countryId'] = 2;
//				$info['say'] = 1;
//				$info['study'] = 0;
//				break;
//			case 'uk';
//				$info['countryId'] = 3;
//				$info['say'] = 1;
//				$info['study'] = 0;
//				break;
//			case 'au';
//				$info['countryId'] = 4;
//				$info['say'] = 1;
//				$info['study'] = 0;
//				break;
//			default:
//				$info['countryId'] = 0;
//				$info['say'] = 0;
//				$info['study'] = 1;
//		}
//
//		return $info;
//	}

    /**
     * 非中国国籍都是说英文，学中文
     * @param $country_id
     * @return array
     */
    public static function getCountryInfoById($country_id)
    {
        //0中国cn 1美国us 2加拿大ca 3 英国  uk 4 澳大利亚 au 5 新加坡
        $info = array();
        $info['countryId'] = $country_id;
        $info['say'] = ($country_id == 0) ? 0 : 1;
        $info['study'] = ($country_id == 0) ? 1 : 0;

        return $info;
    }

    /**
     * 根据IP获取国家的ID编号
     * @param $ip
     * @return int
     */
    public static function getCountryId( $ip )
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=".$ip);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        $json_string = curl_exec($curl);
        curl_close($curl);

        $ret = json_decode($json_string, true);
        $countryStr = $ret['country'];

        if($countryStr == "中国")
        {
            return 0;
        }
        else
        {
            if( isset(self::$country_list[$countryStr]) )
            {
                return self::$country_list[$countryStr];
            }
            else
            {
                return 1; //默认美国
            }
        }
    }
    /**
     * 根据语言确定国家信息
     * @param $lang
     * @return array
     */

    public static function getCountryInfoByLang($lang, $ip ='')
    {
        //0中国cn 1美国us 2加拿大ca 3 英国  uk 4 澳大利亚 au 5 新加坡
        $info = array();
        $info['countryId'] = ($lang == 0) ? 0 : 1; //如果说汉语，那么就是中国的，否则默认是美国
        $info['say'] =$lang;
        $info['study'] = ($lang == 0) ? 1 : 0;

        if( isset($ip) && strlen($ip) > 0)
        {
            if($lang == 0) //不做修改
            {
            }
            else
            {
                $tmpid = self::getCountryId($ip);
                if($tmpid == 0) //选择非汉语区，但是IP是中国的，那么不做变化，默认美国
                {

                }
                else
                {
                    $info['countryId'] =  $tmpid;  //选择的非汉语区，并且IP不是中国，那么返回相应的值
                }
            }
        }

        return $info;
    }
	/**
	 * 检测链接是否是SSL连接
	 * 
	 * @return boolean
	 */
	function is_SSL(){
		if(!isset($_SERVER['HTTPS']))
			return FALSE;
	 	if($_SERVER['HTTPS'] === 1){  //Apache
			return TRUE;
		}elseif($_SERVER['HTTPS'] === 'on'){ //IIS
			return TRUE;
		}elseif($_SERVER['SERVER_PORT'] == 443){ //其他
			return TRUE;
		}
		return FALSE;
	}
	
	public static function str_rot47($str) {
		return strtr ( $str, '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~', 'PQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNO' );
	}
	public static function str_rot47back($str) {
		return strtr ( $str, 'PQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNO', '!"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~' );
	}
	public static function rot13($str) {
		return strtr ( $str, "NOPQRSTUVWXYZnopqrstuvwxyzABCDEFGHIJKLMabcdefghijklm", "ABCDEFGHIJKLMabcdefghijklmNOPQRSTUVWXYZnopqrstuvwxyz" );
	}
	public static function rot13back($str) {
		return strtr ( $str, "ABCDEFGHIJKLMabcdefghijklmNOPQRSTUVWXYZnopqrstuvwxyz", "NOPQRSTUVWXYZnopqrstuvwxyzABCDEFGHIJKLMabcdefghijklm" );
	}

	public static function getNpc()
	{
		$array = include(ROOT_PATH . '/inf/npc_config.php');
		$arr = array();
		foreach($array as $a1)
		{
			foreach($a1 as $a2)
			{
				foreach($a2 as $a3)
				{
					$arr[] = $a3['uid'];
				}
			}
		}

		return $arr;
	}

    //百度翻译接口 -- 百度旧有的翻译 已经正式停用
//    public static function language_baidu($value,$from="auto",$to="auto")
//    {
//        $value_code=urlencode($value);
//        #首先对要翻译的文字进行 urlencode 处理
//        $appid="NvDqn79dKk9y3o5DGVoEQRBG";
//        #您注册的API Key
//        $languageurl = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=" . $appid ."&q=" .$value_code. "&from=".$from."&to=".$to;
//        #生成翻译API的URL GET地址
//        $text=json_decode(self::language_text($languageurl));
//        $text = $text->trans_result;
//        return $text[0]->dst;
//    }

    public static function language_text($url)  #获取目标URL所打印的内容
    {
        if(!function_exists('file_get_contents')) {
            $file_contents = file_get_contents($url);
        } else {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }
        return $file_contents;
    }
    //下面是百度翻译相关接口，当前我们的合同期是到2016年8月5号，到时候记得续签
    //每个月要注意付费；申请baidu翻译的时候，有个IP列表限制，申请时给出了4个IP：两个aws、两个aliyun
    //翻译入口
    public static function translate($query, $from = "auto", $to = "auto")
    {
        $args = array(
            'q' => $query,
            'appid' => APP_ID,
            'salt' => rand(10000,99999),
            'from' => $from,
            'to' => $to,

        );
        $args['sign'] = self::buildSign($query, APP_ID, $args['salt'], SEC_KEY);
        $ret = self::call(URL, $args);
        $ret = json_decode($ret, true);
        return $ret;
    }

    //加密
    public static function buildSign($query, $appID, $salt, $secKey)
    {
        $str = $appID . $query . $salt . $secKey;
        $ret = md5($str);
        return $ret;
    }

    //发起网络请求
    public static function call($url, $args=null, $method="post", $testflag = 0, $timeout = CURL_TIMEOUT, $headers=array())
    {
        $ret = false;
        $i = 0;
        while($ret === false)
        {
            if($i > 1)
                break;
            if($i > 0)
            {
                sleep(1);
            }
            $ret = self::callOnce($url, $args, $method, false, $timeout, $headers);
            $i++;
        }
        return $ret;
    }

    public static function callOnce($url, $args=null, $method="post", $withCookie = false, $timeout = CURL_TIMEOUT, $headers=array())
    {
        $ch = curl_init();
        if($method == "post")
        {
            $data = self::convert($args);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        else
        {
            $data = self::convert($args);
            if($data)
            {
                if(stripos($url, "?") > 0)
                {
                    $url .= "&$data";
                }
                else
                {
                    $url .= "?$data";
                }
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if($withCookie)
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    public static function convert(&$args)
    {
        $data = '';
        if (is_array($args))
        {
            foreach ($args as $key=>$val)
            {
                if (is_array($val))
                {
                    foreach ($val as $k=>$v)
                    {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                }
                else
                {
                    $data .="$key=".rawurlencode($val)."&";
                }
            }
            return trim($data, "&");
        }
        return $args;
    }
    //百度翻译接口如上

    /**
     * @param $src_file      源文件完整路径
     * @param $desc_path 目标文件路径 - 文件名保持不变，只是提供路径即可
     * @param $pem_path scp需要提供目标服务器的pem证书，且必须为700权限 【chmod 700 ***.pem文件】
     * @param string $desc_ip 目标服务器IP地址，暂时静态服务器为：172.31.1.116 （内网地址）
     * @param string $desc_user aws scp拷贝必须是ec2-user，对应的目标服务器路径 要修改权限【chmod ec2-user:ec2-user desc_path】
     *
     * 举例：scp -i /home/ec2-user/snaplingo.pem /data/wwwroot/work/tr.txt ec2-user@172.31.1.116:/data/wwwroot/work/
     * php调用scp，执行者为www，会执行失败。 一种解决方案：20150915【php SCP 执行失败】.txt；再着就是ssh2扩展，或者直接php post，
     * 在静态资源服务器，php文件接受并重新写磁盘文件。
     */
    public static function scp_static_res($src_file, $desc_path, $pem_path = STATIC_SERVER_PEMFILE, $desc_ip =STATIC_SERVER_IP )
    {
        //如果没有设置目标路径IP，则返回
        if(!isset($desc_ip['1']))
        {
            return;
        }
        $cmd = "scp -i {$pem_path} {$src_file} ec2-user@{$desc_ip}:{$desc_path}";
        system($cmd);
    }

    //上传单文件到服务器指定目录
    //注：也可以增加多文件上传的版本，但复杂了，单文件来搞吧
    public static function my_upload_file( $filepath, $descpath )
    {
        if (!isset($filepath) || !isset($descpath)) {
            return false;
        }
        if (strlen($filepath) == 0 || strlen($descpath) == 0) {
            return false;
        }
        if (!file_exists($filepath)) {
            return false;
        }
        //php 5.6 + 版本，必须用CURLFile类
        if (class_exists('\CURLFile')) {
            $field = array(
                'file' => new \CURLFile(realpath($filepath)),
                'src_path' => $filepath, //当前源地址和目标地址，都是一个；后续真到瓶颈了，或者静态资源服务器增多，或者目录更改，再增加额外参数做区分
                'desc_path' => $descpath
            );
        } else {
            $field = array(
                'file' => '@' . realpath($filepath),
                'src_path' => $filepath, //当前源地址和目标地址，都是一个；后续真到瓶颈了，或者静态资源服务器增多，或者目录更改，再增加额外参数做区分
                'desc_path' => $descpath
            );
        }
        $ch = curl_init("http://172.31.9.43/server.php"); //静态资源服务器内网IP
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field);
        $info = curl_exec($ch); // 此时执行结果为bool变量
        curl_close($ch);
        return $info;
    }
    //共用写文件接口
    //$file -完整文件名称； $istream -二进制文件流
    public static function my_write_file($file, $istream)
    {
        if(empty($istream))
        {
            return false;
        }
        if (@($fp = fopen($file, 'w+'))) {
            fwrite($fp, $istream);
            fclose($fp);
            unset($istream);
            return true;
        }
        return false;
    }

    public static function log2db($uid, $msg, $type )
    {
        if (!isset($msg['0'])) {
            return;
        }
        $userGuestBookService = ServiceLocator::getUserGuestBookService();
        @$userGuestBookService->addGusetBook( $uid, $msg, $type );
    }

    public static function log2warn($uid, $msg, $type )
    {
        if (!isset($msg['0'])) {
            return;
        }
        $userGuestBookService = ServiceLocator::getUserGuestBookService();
        @$userGuestBookService->addWarnLog( $uid, $msg, $type );
    }

    public static function log2file($log, $name = null)
    {
        $log = $log . '#' . date('Y-m-d H:i:s', time()) . "#" . microtime(true);
        if ($name) {
            error_log($log . "\n", 3, "/data/wwwroot/php/{$name}.log");
        } else {
            error_log($log . "\n", 3, "/data/wwwroot/php/debug.log");
        }
    }

    public static function log2file_tmp($log, $process_id)
    {
        error_log($log . "\n", 3, "/data/wwwroot/php/debug_{$process_id}.log");
    }

    public static function json_encode_no_zh($arr) {
        $str = str_replace ( "\\/", "/", json_encode ( $arr ) );
        $search = "#\\\u([0-9a-f]+)#ie";

        if (strpos ( strtoupper(PHP_OS), 'WIN' ) === false) {
            $replace = "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))";//LINUX
        } else {
            $replace = "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))";//WINDOWS
        }
        return preg_replace ( $search, $replace, $str );
    }

    public static function great_than_version()
    {
        //判断php版本是否大于等于5.2.9 以前版本的array_unique只需第一个参数
        $php_version = explode('-', phpversion());
        $php_version = $php_version[0];
        $php_version_ge529 = strnatcasecmp($php_version, '5.4.0') >= 0 ? true : false; //=0表示版本为5.2.9  ＝1表示大于5.2.9 =-1表示小于5.2.9
        return $php_version_ge529;
    }

    public static function get_display_str( $str )
    {
        $str = str_replace('\n','#@$',$str);
        $str = stripslashes($str);
        //$str = str_replace("/n","\n",$str);
        return $str;
    }

    public static function get_display_str_old( $str )
    {
        $str = str_replace('\n','/n',$str);
        $str = stripslashes($str);
        $str = str_replace("/n","\n",$str);
        return $str;
    }

    public static  function get_zone_area($zone, $desc = false)
    {
        if(isset(self::$zone_list[$zone]))
        {
            if ($desc) {
                return self::$zone_list[$zone][1];
            } else {
                return self::$zone_list[$zone][0];
            }
        }
        return '';
    }

	/**
	 * 获取客户端IP
	 * @return string
	 */
	public static function getRemoteClientIp() {
		if (getenv ( "HTTP_CLIENT_IP" ) && strcasecmp ( getenv ( "HTTP_CLIENT_IP" ), "unknown" )) {
			$ip = getenv ( "HTTP_CLIENT_IP" );
		} elseif (getenv ( "HTTP_X_FORWARDED_FOR" ) && strcasecmp ( getenv ( "HTTP_X_FORWARDED_FOR" ), "unknown" )) {
			$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
		} elseif (getenv ( "REMOTE_ADDR" ) && strcasecmp ( getenv ( "REMOTE_ADDR" ), "unknown" )) {
			$ip = getenv ( "REMOTE_ADDR" );
		} elseif (isset ( $_SERVER ['REMOTE_ADDR'] ) && $_SERVER ['REMOTE_ADDR'] && strcasecmp ( $_SERVER ['REMOTE_ADDR'], "unknown" )) {
			$ip = $_SERVER ['REMOTE_ADDR'];
		} else {
			$ip = "unknown";
		}
		return ($ip);
	}

	/**
	 * HTTP请求
	 * @param $url
	 * @param int $timeout
	 * @return HTML
	 */
	public static function curl_get_contents($url, $timeout=3){
		$curlHandle = curl_init();
		curl_setopt( $curlHandle , CURLOPT_URL, $url );
		curl_setopt( $curlHandle , CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curlHandle , CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $curlHandle , CURLOPT_SSL_VERIFYPEER , false );
		$result = curl_exec( $curlHandle );
		curl_close( $curlHandle );
		return $result;
	}

	/**
	 * 获取IP信息 新浪接口
	 * @param $ip
	 * @return JsonObj|null
	 * JsonObj:
	 *  {"ret":1,"start":-1,"end":-1,"country":"中国","province":"北京","city":"北京","district":"","isp":"","type":"","desc":""}
	 */
	public static function getJsonObjForSinaApi($ip){
		$url = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=".$ip;
		$ip_json = self::curl_get_contents($url);
		$obj = json_decode($ip_json);
		if ($obj->ret == "1") return  $obj;
		return null;
	}

	/**
	 * 获取IP信息 ipinfodb接口
	 * 注意如果更换服务器需更换APIkey
	 * @param $ip
	 * @return JsonObj|null
	 * JsonObj:
	 *{
	 *  "statusCode": "OK",
	 *  "statusMessage": "",
	 *  "ipAddress": "213.251.162.44",
	 *  "countryCode": "FR",
	 *  "countryName": "France",
	 *  "regionName": "Nord-Pas-de-Calais",
	 *  "cityName": "Roubaix",
	 *  "zipCode": "59100",
	 *  "latitude": "50.6942",
	 *  "longitude": "3.17456",
	 *  "timeZone": "+01:00"
	 *}
	 *
	 */
	public static function getJsonObjForIpInfoDbApi($ip)
	{
		$apiKey = 'b60e5384e16a365bbf031d0330d337cd0845b0138a1c53af7c31edf6b7e81ca7';
		$url = "http://api.ipinfodb.com/v3/ip-city/?key=$apiKey&ip=$ip&format=json";
		$ip_json = self::curl_get_contents($url);
		$obj = json_decode($ip_json);
		if ($obj->statusCode == "OK") return  $obj;
		return null;
	}


	/**
	 * 根据坐标获取时区id : America/Los_Angeles
	 * 注意如果更换服务器需更换APIkey
	 * @param $Lcation 37.77493,-122.41942
	 * @return mixed|null
     */
	public static function getTimeZoneIdByLocation($Lcation)
	{
		$apiKey = '';
		$timeNow = time();
		$url = "https://maps.googleapis.com/maps/api/timezone/json?location={$Lcation}&timestamp={$timeNow}&sensor=false&key={$apiKey}";
		$ip_json = self::curl_get_contents($url);
		$obj = json_decode($ip_json);
		if ($obj->status == "OK") {
			return  $obj->timeZoneId;
		}
		return null;
	}

	/**
	 * 获取时区
	 * @return mixed
	 */
	public static function getIPTimeZone()
	{
		$ip = self::getRemoteClientIp();

		//这里可以获取IP的城市坐标等信息
		$IPInfoJsonObj = self::getJsonObjForIpInfoDbApi($ip);
		
		// 2016-07-22 AZhi -- START
		//如果失败重新请求 3次
		$wi = 1;
		do {
			if($IPInfoJsonObj) break;
			$IPInfoJsonObj = self::getJsonObjForIpInfoDbApi($ip);
			$wi ++;
		} while ($wi < 4);
		// 2016-07-22 AZhi -- END

		if($IPInfoJsonObj) {

			//用谷歌的API获取时区ID
			$location = "{$IPInfoJsonObj->latitude},{$IPInfoJsonObj->longitude}";
			$time_zone_id = self::getTimeZoneIdByLocation($location);
			
			// 2016-07-22 AZhi -- START
			$wi = 1;
			do {
				if($time_zone_id) break;
				$time_zone_id = self::getTimeZoneIdByLocation($location);
				$wi ++;
			} while ($wi < 4);
			// 2016-07-22 AZhi -- END

			//判断当前时区是否有该ID
			if($time_zone_id)
			{
				//设置缓存 以避免每次都请求GOOGLE API
				foreach( self::$zone_list as $key => $value)
					if(isset($value[0]) && $value[0] == $time_zone_id)
						return $key;
			}

		}
		return 2;
	}

    //判断文件是否存在，支持判断本地和服务器
    public  static function my_file_exists($file)
    {
        if(preg_match('/^http:\/\//',$file)){
            //远程文件
            if(ini_get('allow_url_fopen')){
                if(@fopen($file,'r')) return true;
            }
            else{
                $parseurl=parse_url($file);
                $host=$parseurl['host'];
                $path=$parseurl['path'];
                $fp=fsockopen($host,80, $errno, $errstr, 10);
                if(!$fp)return false;
                fputs($fp,"GET {$path} HTTP/1.1 \r\nhost:{$host}\r\n\r\n");
                if(preg_match('/HTTP\/1.1 200/',fgets($fp,1024))) return true;
            }
            return false;
        }
        return file_exists($file);
    }

    public  static function get_process_id()
    {
        return getmypid();
    }

    public static function getRandNpcId($country=1, $db=1)
    {
        if ($country > 0) {
            $country = 0; //目标用户的ID
        }else{
            $country = 1;
        }
        $path = ROOT_PATH . '/inf/npc_config.php';
        $npc_array = include($path);
        $array = $npc_array[$db][$country];
        $index = time() % count($array);
        return $array[$index]['uid'];
    }
    
    public static function json_exit_success($data) {
		return array('status'=>array('code'=>1,'msg'=>""),'data'=>$data);
	}


	public static function json_exit_error($code,$msg) {
		return array('status'=>array('code'=>$code,'msg'=>$msg),'data'=>'');
	}
	
    
    /**
     * 短信宝 短信验证接口 请不要谁便修改短信模板! 如必须修改请登录短信宝报备模板走VIP 通道
     * 单个手机号码每天只能发4条短信 如果测试要发多条则去短信宝添加 白名单
     * @parame {Number} phone 手机号码
     */
    public static function send_verify_code($phone)
    {
	    $ip = self::getClientIP();
        $code = mt_rand(1000,9999); 
        $content="验证码：{$code}。验证码有效期1天。";//要发送的短信内容
        return array('res'=>self::send_sms_smsbao($phone,$content),'code'=>$code);
    }
    
    public static function send_sms_smsbao($phone,$content)
    {
    	//短信宝KEY
	    $cocsms_user     = '';
	    $cocsms_pass_md5 = '';
        $smsapi = "http://api.smsbao.com/";
        $sendurl = $smsapi."sms?u={$cocsms_user}&p={$cocsms_pass_md5}&m={$phone}&c=".urlencode($content);
        return self::curl_get_contents($sendurl);
    }

    public static function getRongyunAppKey()
    {
        $rongyun = new ServerAPI();
        return $rongyun->getAppKey();
    }

    /**
     * @param $str1
     * @param $str2
     * @return bool
     */
    public static function getSimilarRatio($str1, $str2)
    {
        $lcs = new LCS();
        @$ratio = $lcs->getLCS($str1,$str2);
        //如果匹配率大于50%，就认为匹配成功
        if( isset($ratio)  && intval($ratio) > 50){
            return true;
        }
        return false;
    }
}
