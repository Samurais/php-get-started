<?php // -*-coding:utf-8; mode:php-mode;-*-
   namespace framework\util;
   use framework\core\Context;

    /*
     * 日志操作类
      * @author xodger@gmail.com
      * @package framework\util
     */

    class Log
    {
            /**
         * 记录基本信息
         *
         * @param array $params 要记录的内容
         */
        public static function info($tip, $params = null)
        {
            self::out('info', $tip, $params);
        }
        public static function expt($tip,$code,$uid,$params)
        {
            return false;
            $mesg = \date("Y-m-d H:i:s",time())."\t";
            $mesg.="uid={$uid},";
            $mesg.="type={$tip},";
            $mesg.="code={$code},";
            if(!empty($params))
            {
                function reduce($key,$value)
                {
                    if(\is_object($key)||\is_array($key))
                        $keyStr=\serialize($key);
                    else
                        $keyStr=(string)$key;
                    if(\is_object($value)||\is_array($value))
                        $valueStr=\serialize($value);
                    else
                        $valueStr=(string)$value;
                    return((string)$keyStr.'='.(string)$valueStr);
                }
                $keys=\array_keys($params);
                $value=\array_values($params);
                $params2str=\array_map('reduce', $keys , $value);
                $mesg.=\is_array($params2str)?\implode('',$params2str):'';
            }

            $fname = Context::getRootPath() . '/log/' . APP_API . \date("Ymd",\time()) . '.log';
            if (!@\is_file($fname)) {
                @\touch($fname);
                @\chmod($fname, 0777);
            }
            \error_log($mesg . "\n",3,$fname);
        }

        private static function out($level, $tip, $params = null)
        {
            $currtime = \time();
            $fname = Context::getRootPath() . '/log/' . APP_API . \date("Ymd",$currtime) . '.log';


    //		$params=array(1,2,3,4,5=>array(1,2,3,123=>222,332=>'fdsa'));
    //		$data=array();
    //		function simplyParams($params)
    //		{
    //			if(is_array($params)||is_object($params))
    //			{
    //				foreach ((array)$params as $key=> $value)
    //				{
    //					if(is_array($value)||is_object($value))
    //					{
    //						$data[]=simplyParams($value);
    //					}
    //					else
    //					{
    //						$data[$key]=$value;
    //					}
    //				}
    //			}
    //			return $data;
    //		}
    //		if($params)
    //		{
    //			$data[]=simplyParams($params);
    //		}
    //		print_r($data);
    //		print_r(self::$_logData);
			date_default_timezone_set("Asia/Chongqing");
            $mesg = \date("Y-m-d H:i:s") . "\tl=" . $level . ",s=".APP_API.",t=". $tip . "\t";
            foreach ( (array)$params as $k => $v ) {
                if(\is_object($v))
                    $v=(array)$v;
                /*if (false || sys_define::LOG_PRESERVE==true) { // convert special character
                    $k = str_replace(array('&','=',','),array('&&','&=','&,'),$k);
                    $v = str_replace(array('&','=',','),array('&&','&=','&,'),$v);
                }*/
                $mesg .= $k . '=' . (string)$v . ',';
            }
            if (!@\is_file($fname)) {
                @\touch($fname);
                @\chmod($fname, 0777);
            }
            \error_log($mesg . "\n",3,$fname);
        }
    }
