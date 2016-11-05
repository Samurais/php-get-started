<?php
namespace framework\util;

use entity;

use \Exception;
use exception\GameException;
/**
 * Log类功能
 * 
 * @package framework\util
 */
class StatMain
{
	const STAT_ON = true;
	
	const SEPARATOR = "\t";
	
	const LOG_DIR = '/log/stat/';

	private static $cache = array();
	
	private static $isBatch = false;
//	const TYPE_LOGIN = 1;
	
	public static function setLogIsBatch()
	{
		self::$isBatch = true;
	}
	
	private static function isok()
	{
		if ( self::STAT_ON )
		{
			return true;
		}
		return false;
	}
	public static function create($uid, $hour, $ref=0)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$hour.self::SEPARATOR.$ref;
		self::log('ncreate', $str);
	}
	
	public static function multicreate($uid, $serverId)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$serverId;
		self::log('multicreate', $str);
	}

	public static function help($uid, $minelapse)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$minelapse;
		self::log('nhelp', $str);
	}
	
	public static function active($uid, $level, $dayelapse, $buydayelapse, $loginelapse, $consume, $friends, $ref=0)
	{
		if (!self::isok()) return;
		$hour = time()%3600;
		$str = $uid.self::SEPARATOR.$level.self::SEPARATOR.$dayelapse.self::SEPARATOR.$buydayelapse.self::SEPARATOR.$loginelapse.self::SEPARATOR.$consume.self::SEPARATOR.$friends.self::SEPARATOR.$ref.self::SEPARATOR.$hour;
		self::log('nactive', $str);
	}
	
	public static function multiactive($uid, $serverId, $level)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$serverId.self::SEPARATOR.$level;
		self::log('multiactive', $str);
	}
	
	public static function snapshot($uid, $level, $ary = array())
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$level;
		foreach($ary as $k=>$v) $str.= self::SEPARATOR.$v;
		self::log('nsnapshot', $str);
	}
	
	public static function levelup($uid, $level, $ct, $ary = array())
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$level;
		foreach($ary as $k=>$v) $str.= self::SEPARATOR.$v;
		self::log('nlevelup', $str);
	}
	
	public static function isrmb($uid, $level, $dayelapse)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$level.self::SEPARATOR.$dayelapse;
		self::log('nisrmb', $str);
	}
	
	public static function buyrmb($uid, $level, $ct, $dayelapse, $sid, $num, $sum)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$level.self::SEPARATOR.$dayelapse.self::SEPARATOR.$sid.self::SEPARATOR.$num.self::SEPARATOR.$sum;
		self::log('nbuyrmb', $str);
	}
	
	public static function multibuyrmb($uid, $serverId, $level, $ct, $dayelapse, $sid, $num, $sum)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$serverId.self::SEPARATOR.$level.self::SEPARATOR.$dayelapse.self::SEPARATOR.$sid.self::SEPARATOR.$num.self::SEPARATOR.$sum;
		self::log('multibuyrmb', $str);
	}
	
	public static function buygold($uid, $level, $ct, $sid, $num, $sum, $paytype = 0)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$level.self::SEPARATOR.$sid.self::SEPARATOR.$num.self::SEPARATOR.$sum.self::SEPARATOR.$paytype;
		self::log('nbuygold', $str);
	}
	
	public static function send($uid, $level, $ct, $sid, $num)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$level.self::SEPARATOR.$sid.self::SEPARATOR.$num;
		self::log('nsend', $str);
	}
	
	public static function apply($uid, $level, $ct, $sid, $num)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$level.self::SEPARATOR.$sid.self::SEPARATOR.$num;
		self::log('napply', $str);
	}
	
	public static function dot($uid, $ct, $sid, $num=1)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$sid.self::SEPARATOR.$num;
		self::log('ndot', $str);
	}
	
	public static function tdot($uid, $ct, $sid, $num=1)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$sid.self::SEPARATOR.$num;
		self::log('tdot', $str);
	}
	
	public static function level($uid, $level, $ct, $sid, $num)
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$level.self::SEPARATOR.$sid.self::SEPARATOR.$num;
		self::log('nlevel', $str);
	}

	public static function event($uid, $ct, $eid, $ary = array())
	{
		if (!self::isok()) return;
		$str = $uid.self::SEPARATOR.$ct.self::SEPARATOR.$eid;
		foreach($ary as $k=>$v) $str.= self::SEPARATOR.$v;
		self::log('nevent', $str);
	}
	
	public static function oper($uid, $ct, $opid, $ary = array())
	{
		if (!self::isok()) return;
		$str = "$uid\t$ct\t$opid";
		foreach($ary as $k=>$v) $str.= "\t".$v;
		self::log('noper', $str);
	}
	
	public static function toper($uid, $ct, $opid, $ary = array())
	{
		if (!self::isok()) return;
		$str = "$uid\t$ct\t$opid";
		foreach($ary as $k=>$v) $str.= "\t".$v;
		self::log('toper', $str);
	}
	
	public static function recharge($uid, $ct, $level, $amt, $diamond,$bdiamond,$cdiamond,$bvouch,$cvouch,$ediamond)
	{
		if (!self::isok()) return;
		$str = "$uid\t$ct\t$level\t$amt\t$diamond\t$bdiamond\t$cdiamond\t$bvouch\t$cvouch\t$ediamond";
		self::$isBatch = false;
		self::log('recharge', $str);
	}
	
	public static function source($uid, $ct, $source, $ref)
	{
		if (!self::isok()) return;
		$str = "$uid\t$ct\t$source\t$ref";
		self::log('source', $str);
	}
	
	private static function log($type, $str)
	{
		self::$cache[] = array(
			'type' => $type,
			'str' => $str
		);
		if (empty(self::$isBatch))
		{
			self::write();
		}
	}
	
	public static function write()
	{
		$t = date("Ymd");
		$bdir = ROOT_PATH . self::LOG_DIR . $t;
		foreach (self::$cache as $arr)
		{
			$dir = $bdir;
			$type = $arr['type'];
			$str = $arr['str'];
			$fname = $dir . '/' . $type . '.log';
			if ($type == "tdot")
			{
				$dir .= '/dot';
				$time = time()>>9<<9;
				$fname = $dir . '/' .$time . ".log";
			}
			elseif ($type == "toper")
			{
				$dir .= '/oper';
				$time = time()>>9<<9;
				$fname = $dir . '/' .$time . ".log";
			}
			
			if (!@file_exists($fname)) {
				
				if (!@file_exists($dir)) {
					$old_umask = @umask(0);
					@mkdir($dir, 0777, true);
					umask($old_umask);
					@chmod($dir, 0777);
				}
				@touch($fname);
				@chmod($fname, 0777);
			}
			error_log( $str."\n", 3, $fname);
		}
		self::$cache = array();
	}
}