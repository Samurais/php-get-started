<?php
namespace framework\helper;
use framework\manager;
use framework\config;
use framework\util\Log;
require_once(ROOT_PATH.'/lib/pheanstalk/pheanstalk_init.php');
/**
 * Beanqueue数据处理类
 * 
 * @author zivn
 * @package framework\helper
 */
class BeanqueueHelper
{
	const TTR = 120;
	/**
     * Beanqueue对象
     *
     * @var 
     */
    private $beanqueue;

	private $bakconf;
    
	/** 
	 * 构造函数
	 * 
	 * @param bool $enable
	 */
	public function __construct($uid)
	{
		//return $this->getBeanqueue($uid);
	}
    
    /**
     * 取得Beanqueue对象
     * 
	 * @return \Beanqueue
	 */
	public function getBeanqueue($id,$bak=false)
	{
		$this->beanqueue = manager\BeanqueueManager::getInstance($id,$bak);
		return $this->beanqueue;
	}
    
	/**
	 * 添加队列
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param int $expiration
	 * @return bool
	 */
	public function addQueue($uid, $type, $len, $pri, $params = array())
	{
		$pary = array(
			'uid' => $uid,
			'type' => $type,
			'param' => $params,
			);
		if (empty($params) || empty($params['serverid'])) return false;
		if (false == ($mc = $this->getBeanqueue($params['serverid']))) return false;
		$jobid = false;
		try {
			$jobid = $mc->put(serialize($pary), $pri, $len, self::TTR);
			if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_add', array('uid'=>$uid,'type'=>$type,'jobid'=>$jobid));
		} catch (\Exception $e) {
			if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_add_fail', array('uid'=>$uid,'type'=>$type,'msg'=>$e->getMessage()));
		}
		if ($jobid) return $jobid;
		return 0;
	}

	public function addQueueOther($tube, $uid, $type, $len, $pri, $params = array())
	{
		$pary = array(
			'uid' => $uid,
			'type' => $type,
			'param' => $params,
			);
		if (empty($params) || empty($params['serverid'])) return false;
		if (false == ($mc = $this->getBeanqueue($params['serverid']))) return false;
		$jobid = false;
		try {
			$mc->useTube($tube);
			$jobid = $mc->put(serialize($pary), $pri, $len, self::TTR);
			$mc->useTube(PROJECT_NAME.'_'.APP_API);
			if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_add', array('tube'=>$tube,'uid'=>$uid,'type'=>$type,'jobid'=>$jobid));
		} catch (\Exception $e) {
			if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_add_fail', array('tube'=>$tube,'uid'=>$uid,'type'=>$type,'msg'=>$e->getMessage()));
		}
		if ($jobid) return $jobid;
		return 0;
	}
	
	public function process($tube, $id, $target = null)
	{
	
		if (false == ($pheanstalk = $this->getBeanqueue($id))) return false;
		
		$watch = PROJECT_NAME.'_'.APP_API;
		if ($tube) $watch = $tube;
		$pheanstalk->watch($watch);
		
		//xhprof_enable();
		$pnum = 0;
		$stime = time();
		while (1) {
			$job = $pheanstalk->reserve(5);
			if (!$job) {
				$etime = time();
				if ($etime - $stime > 100) break;
				sleep(5);
				continue;
			}
			try {
				$pheanstalk->delete($job);
			} catch (\Exception $e) {
				//
			}
			$p = unserialize($job->getData());
			if ($p['type']=='quit') break;
			$jobid = $job->getId();
			if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_process', array('uid'=>$p['uid'],'qid'=>$jobid,'type'=>$p['type']));
			$className = "\\service\\beanqueue\\CopyJoinService";
			$obj = new $className;
			$obj->process($jobid, $p['uid'], $p['param']);
			unset($obj);
			
			$pnum++;
			if ($pnum>20) {
				$pnum = 0;
				$etime = time();
				if ($etime - $stime > 100) break;
			}
			if (isset($GLOBALS['flag_exit']) && $GLOBALS['flag_exit']==true) break;
			unset($job);
		}
		
	    //$xhprof_data    =   xhprof_disable();
	    //$xhprof_runs    =   new XHProfRuns_Default();
	    //$run_id         =   $xhprof_runs->save_run($xhprof_data, 'xhprof_foo');
	    //Log::info('prof_xh', array('url'=>'http://'.APP_API.'.app.tuitui8.com/xhprof_html/index.php?run='.$run_id.'&source=xhprof_foo'));
	}

	public function process_queue($id, $target = null)
	{
	
		if (false == ($pheanstalk = $this->getBeanqueue($id))) return false;
		
		$watch = PROJECT_NAME.'_'.APP_API;
		if ($target) $watch.= '_'.$target;
		$pheanstalk->watch($watch);
		
		//xhprof_enable();
		$pnum = 0;
		$stime = time();
		while (1) {
			$job = $pheanstalk->reserve(5);
			if (!$job) {
				$etime = time();
				if ($etime - $stime > 100) break;
				sleep(5);
				continue;
			}
			try {
				$pheanstalk->delete($job);
			} catch (\Exception $e) {
				//
			}
			$p = unserialize($job->getData());
			if ($p['type']=='quit') break;
			$jobid = $job->getId();
			if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_process', array('uid'=>$p['uid'],'qid'=>$jobid,'type'=>$p['type']));
			$className = "\\service\\beanqueue\\CopyJoinService";
			$obj = new $className;
			$obj->process($jobid, $p['uid'], $p['param']);
			unset($obj);
			
			$pnum++;
			if ($pnum>20) {
				$pnum = 0;
				$etime = time();
				if ($etime - $stime > 100) break;
			}
			if (isset($GLOBALS['flag_exit']) && $GLOBALS['flag_exit']==true) break;
			unset($job);
		}
		
	    //$xhprof_data    =   xhprof_disable();
	    //$xhprof_runs    =   new XHProfRuns_Default();
	    //$run_id         =   $xhprof_runs->save_run($xhprof_data, 'xhprof_foo');
	    //Log::info('prof_xh', array('url'=>'http://'.APP_API.'.app.tuitui8.com/xhprof_html/index.php?run='.$run_id.'&source=xhprof_foo'));
	}

	public function process_bak_queue($id,$type)
	{
		if (false == ($pheanstalk = $this->getBeanqueue($id,true))) return false;
		
		$pheanstalk->watch(PROJECT_NAME.'_'.APP_API);
		
		$pnum = 0;
		$stime = time();
		while (1) {
			$job = $pheanstalk->reserve(5);
			if (!$job) {
				sleep(5);
				continue;
			}
			try {
				$pheanstalk->delete($job);
			} catch (\Exception $e) {
				//
			}
			$p = unserialize($job->getData());
			if ($p['type']=='quit') break;
			$jobid = $job->getId();
			if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_process', array('uid'=>$p['uid'],'qid'=>$jobid,'type'=>$p['type']));
			$obj = new \service\BeanqueueService($p['uid'], $type);
			$obj->$p['type']($jobid, $p['uid'], $p['param']);
			unset($obj);
			
			if (isset($GLOBALS['flag_exit']) && $GLOBALS['flag_exit']==true) break;
			unset($job);
		}
	}
	
	// ahead_time为0表示立即完成，否则是减少ahead_time等待时间
	public function process_ahead($uid, $qid, $ahead_time = 0, $params = array(),$type=false)
	{
		if (false == ($mc = $this->getBeanqueue($uid))) return false;
		$mc->watch(PROJECT_NAME.'_'.APP_API);
		
		$p = $this->peek($mc, $uid, $qid, $find);
		if (!$p) return false;
		if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_ahead', array('uid'=>$uid,'qid'=>$qid,'ahead'=>$ahead_time,'find'=>$find));
		
		$process = false;
		try {
			if ($ahead_time<0) {
				$response = $mc->ignore($qid, 0-$ahead_time, 1);
			} else {
				$response = $mc->ignore($qid, $ahead_time, 0);
			}
			switch ($response->getResponseName()) {
			case 'DELETED':
				$process = true;
				break;
			case 'REDELAYED':
				return true;
				break;
			case 'NOT_FOUND':
				if ($find!=-1) {
					$mc2 = $this->getBeanqueue($find,true);
					if (!$mc2) return false;
					if ($ahead_time<0) {
						$response2 = $mc2->ignore($qid, 0-$ahead_time, 1);
					} else {
						$response2 = $mc2->ignore($qid, $ahead_time, 0);
					}
					switch ($response2->getResponseName()) {
					case 'DELETED':
						$process = true;
						break;
					case 'REDELAYED':
						return true;
						break;
					case 'NOT_FOUND':
						return false;
						break;
					default:
						return false;
						break;
					}
				} else {
					return false;
				}
				break;
			default:
				return false;
				break;
			}
		} catch (\Exception $e) {
			//
		}
		
		if ($process) {
			$obj = new \service\BeanqueueService($p['uid'], $type);
			$obj->$p['type']($qid, $p['uid'], $pas);
			return true;
		}
		return false;
	}
	
	public function getinfo($uid, $qid)
	{
		if (false == ($mc = $this->getBeanqueue($uid))) return false;
		$mc->watch(PROJECT_NAME.'_'.APP_API);

		return $this->peek($mc, $uid, $qid, $find);
	}
	
	public function delete($uid, $qid)
	{
		if (false == ($mc = $this->getBeanqueue($uid))) return false;
		$mc->watch(PROJECT_NAME.'_'.APP_API);
	
		$p = $this->peek($mc, $uid, $qid, $find);
		if (!$p) return false;
		if (\defined("LOG_QUERY")) \common\Log::info('beanqueue_delete', array('uid'=>$uid,'qid'=>$qid,'find'=>$find));
		
		try {
			$response = $mc->ignore($qid, 0, 0);
			switch ($response->getResponseName()) {
			case 'DELETED':
				return true;
				break;
			case 'NOT_FOUND':
				if ($find!=-1) {
					$mc2 = $this->getBeanqueue($find,true);
					if (!$mc2) return false;
					$response2 = $mc2->ignore($qid, 0, 0);
					switch ($response2->getResponseName()) {
					case 'DELETED':
						return true;
						break;
					case 'NOT_FOUND':
						return false;
						break;
					default:
						return false;
						break;
					}
				} else {
					return false;
				}
				break;
			default:
				return false;
				break;
			}
		} catch (\Exception $e) {
			//
		}
		
		return false;
	}
	
	private function peek($mc, $uid, $qid, &$find)
	{
		$find = -1;
		$job = false;
		try {
			$job = $mc->peek($qid);
		} catch (\Exception $e) {
			//
		}
		if (!$job) {
			$this->bakconf = manager\BeanqueueManager::getAllConf(true);
			if ($this->bakconf != null && is_array($this->bakconf)) {
				for ($i=0; $i<count($this->bakconf); $i++) {
					$mc2 = $this->_getMc($this->bakconf[$i]);
					if (!$mc2) continue;
					try {
						$job = $mc2->peek($qid);
					} catch (\Exception $e) {
						//
					}
					if ($job) {
						$p = unserialize($job->getData());
						if (!$p || !isset($p['uid']) || $p['uid']!=$uid) {
							$job = false;
							continue;
						}
						$find = $i;
						break;
					}
				}
			}
			if ($find==-1) return false;
		} else {
			$p = unserialize($job->getData());
			if (!$p || !isset($p['uid']) || $p['uid']!=$uid) return false;
		}
		return $p;
	}
}
