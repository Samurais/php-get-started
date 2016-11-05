<?php
namespace framework\helper;

/**
 * 联合TokyoTyrant数据处理类
 *
 * @author zivn
 * @package framework\helper
 */
class MultiTokyoTyrantHelper
{

    private static $instance = array();
    private $conf;
    private $switch = false;
    private $auto = false;
    private $usemem = false;
	private $usemembak = false;
	private $usesyncmem = false;

    public function __construct($conf, $switch)
	{
		$this->conf = $conf;
		$this->switch = $switch;
	}
	
    public function isNull($id, $key = '')
	{
		if (defined('NO_MEMCACHED')) return true;
		if (false == ($mc = $this->getMc($id))) return false;
		$code = $mc->getResultCode();
		if ($code == \Memcached::RES_NOTFOUND) return true;
		if(\defined("LOG_QUERY")) \common\Log::info('isnull', array('id'=>$id,'code'=>$code,'key'=>$key));
		throw new \common\GameException('1111isnull id ['. $id. '],code [' . $code . '],key [' . $key . ']');
		//if ($code == \Memcached::RES_SUCCESS) return true;
		return false;
	}
	
	public function useDb($rev = array())
	{
		if (empty($rev))
		{
			$ret = array($this->usemem, $this->usemembak, $this->usesyncmem);
			
			$this->usemem = false;
			$this->usemembak = false;
			$this->usesyncmem = false;
			
			return $ret;
		}
		else 
		{
			list($this->usemem, $this->usemembak, $this->usesyncmem) = $rev;
			return ;
		}
	}

	public function isLoadNull($id, $key = '')
	{
		if (defined('NO_MEMCACHED')) return true;
		if ($this->usemem) {
			$this->usemem = false;
			if (false == ($mc = $this->getMc($id))) return false;
			$this->usemem = true;
		} else {
			if (false == ($mc = $this->getMc($id))) return false;
		}
		$code = $mc->getResultCode();
		if ($code == \Memcached::RES_NOTFOUND) return true;
		if ($code == \Memcached::RES_SUCCESS) return true;
		if(\defined("LOG_QUERY")) \common\Log::info('isnullload', array('id'=>$id,'code'=>$code,'key'=>$key));
		throw new \common\GameException('1111isnullload id ['. $id. '],code [' . $code . '],key [' . $key . ']');
		return false;
	}

	private function isBakNull($id, $key = '')
	{
		if (defined('NO_MEMCACHED')) return true;
		if (false == ($mc = $this->getMc($id))) return true;
		$code = $mc->getResultCode();
		if ($code == \Memcached::RES_NOTFOUND) return true;
		if(\defined("LOG_QUERY")) \common\Log::info('isbaknull', array('id'=>$id,'code'=>$code,'key'=>$key));
		throw new \common\GameException('1111isbaknull id ['. $id. '],code [' . $code . '],key [' . $key . ']');
		//if ($code == Memcached::RES_SUCCESS) return true;
		return false;
	}

	private function isSyncMemNull($id, $key = '')
	{
		if (defined('NO_MEMCACHED')) return true;
		if (false == ($mc = $this->getMc($id))) return true;
		$code = $mc->getResultCode();
		if ($code == \Memcached::RES_NOTFOUND) return true;
		if(\defined("LOG_QUERY")) \common\Log::info('issyncnull', array('id'=>$id,'code'=>$code,'key'=>$key));
		throw new \common\GameException('1111issyncnull id ['. $id. '],code [' . $code . '],key [' . $key . ']');
		//if ($code == Memcached::RES_SUCCESS) return true;
		return false;
	}

    public function useMem()
    {
        $this->usemem = true;
    }

 	private function getMemConf($id)
	{
		$conf = $this->conf;
		$memidx = $this->switch['memidx'];
		if (0 == $memidx) return $conf;
		if (!isset($conf['mem'.$memidx])) {
			throw new \common\GameException('cfgwrong func=getMemConf,pos=1');
			return $conf;
		}
		$count = \count($conf['mem'.$memidx]);
		if ($count==0) {
			throw new \common\GameException('cfgwrong func=getMemConf,pos=2');
			return $conf;
		}

		if (\strlen($id) > 8) $id = \substr($id, -8); // facebook的uid超过int范围
		$id = (int)$id;
		$serverid = $id % $count;
		return $conf['mem'.$memidx][$serverid];
	}
	private function getMemBakConf($id)
	{
		$conf = $this->conf;
		if (0 == $this->switch['memidx']) return false;
		$memidx = ($this->switch['memidx']==1) ? 2 : 1;
		if ($this->switch['membak']==false) return false;
		if (!isset($conf['mem'.$memidx])) {
			throw new \common\GameException('cfgwrong func=getMemBakConf,pos=1');
			return false;
		}
		$count = \count($conf['mem'.$memidx]);
		if ($count==0) {
			throw new \common\GameException('cfgwrong func=getMemBakConf,pos=2');
			return false;
		}

		if (\strlen($id) > 8) $id = \substr($id, -8); // facebook的uid超过int范围
		$id = (int)$id;
		$serverid = $id % $count;
		return $conf['mem'.$memidx][$serverid];
	}
	private function getSyncMemConf($id)
	{
		$conf = $this->conf;
		if ($this->switch['syncmem']==false) return false;
		if (!isset($conf['syncmem'])) {
			throw new \common\GameException('cfgwrong func=getSyncMemConf,pos=1');
			return false;
		}
		$count = \count($conf['syncmem']);
		if ($count==0) {
			throw new \common\GameException('cfgwrong func=getSyncMemConf,pos=2');
			return false;
		}

		if (\strlen($id) > 8) $id = \substr($id, -8); // facebook的uid超过int范围
		$id = (int)$id;
		$serverid = $id % $count;
		return $conf['syncmem'][$serverid];
	}


    private function getMc($id)
    {
        if ($this->usemembak) {
			$conf = $this->getMemBakConf($id);
			if (!$conf) return false;
		} else if ($this->usesyncmem) {
			$conf = $this->getSyncMemConf($id);
			if (!$conf) return false;
		} else {
			if ($this->usemem) {
				$conf = $this->getMemConf($id);
			} else {
				$conf = $this->conf;
			}
		}
        if (\defined("LOG_QUERY")) \common\Log::info('getMc_conf', array('conf'=>\json_encode($conf)));
		return $this->_getMc($conf);
    }

    private function _getMc($conf)
	{
        if (\defined("LOG_QUERY")) \common\Log::info('getMc_start', array('conf'=>\json_encode($conf)));
		$host = $conf['host'].'_'.$conf['port'];
		if (isset(self::$instance[$host])) {
			if (!self::$instance[$host]['enable']) return false;
			if (\is_object(self::$instance[$host]['mc'])) return self::$instance[$host]['mc'];
		}

		if (defined('NO_MEMCACHED')) {
			self::$instance[$host]['mc'] = new \Memcache();
		} else {
            if (\defined("LOG_QUERY")) \common\Log::info('getMc_conn', array('conf'=>\json_encode($conf)));
			self::$instance[$host]['mc'] = new \Memcached();
			if (!isset($conf['auto']) || $conf['auto']==0) {
				self::$instance[$host]['mc']->setOption(\Memcached::OPT_COMPRESSION, false);
				self::$instance[$host]['mc']->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_IGBINARY);
			} else {
				$this->auto = true;
			}
		}
		self::$instance[$host]['enable'] = false;
		if ($conf['enable']) {
			if (@self::$instance[$host]['mc']->addServer($conf['host'], $conf['port'])) self::$instance[$host]['enable'] = true;
		}

		if (!self::$instance[$host]['enable']) return false;
        if (\defined("LOG_QUERY")) \common\Log::info('getMc_end', array('conf'=>\json_encode($conf)));
		return self::$instance[$host]['mc'];
	}

    public function nget($id,$key, &$cas_token = null)
    {
        if (false == ($mc = $this->getMc($id))) return false;
        if (\defined("LOG_QUERY")) \common\Log::info('memcachedihash', array('name'=>'get','key'=>$key,"usemem"=>$this->usemem,"usemembak"=>$this->usemembak,"usesyncmem"=>$this->usesyncmem));
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $start_time = \common\Utils::getMicroTime();
        }
        $data = $mc->get($key, null, $cas_token);
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $end_time = \common\Utils::getMicroTime();
            $elapse = $end_time - $start_time;
            if(\defined("LOG_QUERY")) \common\Log::info('prof_tc', array('act'=>'get','elapse'=>$elapse,'key'=>$key));
        }
        return $data;
    }
    
	public function delete($id,$key)
    {
        if (false == ($mc = $this->getMc($id))) return false;
        $ret = $mc->delete($key);
        return $ret;
    }

    public function ngetMulti($id, $keys, &$castokens)
    {
        if (false == ($mc = $this->getMc($id))) return false;
        if (\defined("LOG_QUERY")) \common\Log::info('memcachedihash', array('name'=>'get','key'=>implode(",", array_values($keys))));
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $start_time = \common\Utils::getMicroTime();
        }
        $data = $mc->getMulti($keys, $castokens);
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $end_time = \common\Utils::getMicroTime();
            $elapse = $end_time - $start_time;
            \common\Log::info('prof_tc', array('act'=>'get','elapse'=>$elapse,'keys'=>  \json_encode($keys)));
        }
        return $data;
    }

    public function nset($id,$key, $data)
    {
        if (false == ($mc = $this->getMc($id))) return false;
        if (\defined("LOG_QUERY")) \common\Log::info('memcachedihash', array('name'=>'set','key'=>$key));
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $start_time = \common\Utils::getMicroTime();
        }
        $ret = $mc->set($key, $data);
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $end_time = \common\Utils::getMicroTime();
            $elapse = $end_time - $start_time;
            \common\Log::info('prof_tc', array('act'=>'set','elapse'=>$elapse,'key'=>  $key));
        }
        return $ret;
    }
    
	public function increment($id, $key, $value = 1)
    {
        if (false == ($mc = $this->getMc($id))) return false;
        if (\defined("LOG_QUERY")) \common\Log::info('memcachedihash', array('name'=>'increment','key'=>$key));
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $start_time = \common\Utils::getMicroTime();
        }
        $ret = $mc->increment($key, $value);
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $end_time = \common\Utils::getMicroTime();
            $elapse = $end_time - $start_time;
            \common\Log::info('prof_tc', array('act'=>'increment','elapse'=>$elapse,'key'=>  $key));
        }
        return $ret;
    }

	public function cas($id,$key, $data, $cas_token)
	{
		if (false == ($mc = $this->getMc($id))) return false;
		if (\defined("LOG_QUERY")) \common\Log::info('memcachedihash', array('name'=>'set','key'=>$key));

		$ret = $mc->cas($cas_token, $key, $data);
		
		if(!$ret)
		{
			@error_log(date('Y-m-d H:i:s') . "\t cas \t" . $id . "\t" . $mc->getResultCode() . "\n", 3, '/tmp/rb.log');
		}

		return $ret;
	}

    public function nsetMulti($id,$keys)
    {
        if (false == ($mc = $this->getMc($id))) return false;
        if (\defined("LOG_QUERY")) \common\Log::info('memcachedihash', array('name'=>'set','key'=>$keys));
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $start_time = \common\Utils::getMicroTime();
        }
        $ret = $mc->setMulti($keys);
        
    	if(!$ret)
		{
			@error_log(date('Y-m-d H:i:s') . "\t set \t" . $id . "\t" . $mc->getResultCode() . "\n", 3, '/tmp/rb.log');
		}
        
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $end_time = \common\Utils::getMicroTime();
            $elapse = $end_time - $start_time;
            \common\Log::info('prof_tc', array('act'=>'set','elapse'=>$elapse,'keys'=> \json_decode($keys)));
        }
        return $ret;
    }

    private function nreplace($id,$key, $data)
    {
        if (false == ($mc = $this->getMc($id))) return false;
        if (\defined("LOG_QUERY")) \common\Log::info('memcachedihash', array('name'=>'replace','key'=>$key));
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $start_time = \common\Utils::getMicroTime();
        }
        $ret = $mc->replace($key, $data);
        if (\defined('PROFILE_TC') && PROFILE_TC){
            $end_time = \common\Utils::getMicroTime();
            $elapse = $end_time - $start_time;
            \common\Log::info('prof_tc', array('act'=>'replace','elapse'=>$elapse,'key'=>  $key));
        }
        return $ret;
    }

	// 只限切换时使用
    private function ndecrement($id, $key)
    {
        if (false == ($mc = $this->getMc($id))) return false;
        if (\defined("LOG_QUERY")) \common\Log::info('memcachedihash', array('name'=>'decrement','key'=>$key));
        $ret = $mc->decrement($key);
        if (!$ret) \common\Log::info('decrement_fail_memcachedihash', array('id'=>$id,'key'=>$key));
        return true;
    }

    public function nload($id,$key)
    {
        if (!$this->usemem) return false;

        // 从后备中读取，没有的话会返回false
        $this->usemembak = true;
        $ret = $this->nget($id,$key);
        if ($ret===false) {
            $ret2 = $this->isBakNull($id, $key);
            $this->usemembak = false;
            if (!$ret2) return false;
        }
        $this->usemembak = false;
        if ($ret!==false) {
            $ret2 = $this->nreplace($id, $key, $ret);
            if (!$ret2) \common\Log::info('load_fail_memcachedihash', array('id'=>$id,'key'=>$key));
        	if ($ret2) {
		        if (\defined('SWITCHTT') && SWITCHTT) {
		        	$this->usemembak = true;
		        	$this->ndecrement($id, $key);
		        	$this->usemembak = false;
		        }
            }
            return $ret;
        }

    	if (\defined('SWITCHTT') && SWITCHTT) {
        	throw new \common\GameException('1111switch id ['. $id. ']');
        	return false;
        }

        // 从同步中读取，没有的话会返回false
        $this->usesyncmem = true;
        $ret = $this->nget($id, $key);
        if ($ret===false) {
            $ret2 = $this->isSyncMemNull($id, $key);
            $this->usesyncmem = false;
            if (!$ret2) return false;
        }
        $this->usesyncmem = false;
        if ($ret!==false) {
            $ret2 = $this->nreplace($id, $key, $ret);
            if (!$ret2) \common\Log::info('load_fail_memcachedihash2', array('id'=>$id,'key'=>$key));
            return $ret;
        }
		
        // 从硬盘读取
        $ret = $this->loadFromDb($id,$key);
        
        $this->usemem = true;
        if ($ret!==false) {
            $ret2 = $this->nreplace($id, $key, $ret);
            if (!$ret2) \common\Log::info('load_fail_memcachedihash3', array('id'=>$id,'key'=>$key));
        }
        return $ret;
    }
    
    /**
     * 从硬盘中读取
     */
    public function loadFromDb($id,$key)
    {
    	// 从硬盘中读取
        $this->usemem = false;
        $p = \strpos($key, '__');
        if ($p){
            $prevkey = \substr($key, 0, $p);
            $nextkey = \substr($key, $p+2);
            $ret = $this->nget($id, $prevkey);
            if ($ret!==false) {
                $len = \strlen($ret);
                while (1) {
                    $ret2 = \unpack("Nksiz/Nvsiz", $ret);
                    if ($len<$ret2['ksiz']+$ret2['vsiz']+8) {
                        if(\defined("LOG_QUERY")) \common\Log::info('unpackerror', array('id'=>$id,'key'=>$key));
                        throw new \common\GameException('1111unpackerror id ['. $id. ']');
                        break;
                    }
                    $tmpkey = \substr($ret, 8, $ret2['ksiz']);
                    if ($tmpkey==$nextkey) {
                        $data = \substr($ret, 8+$ret2['ksiz'], $ret2['vsiz']);
                        $ret = $data;
                        break;
                    }
                    $ret = \substr($ret, 8+$ret2['ksiz']+$ret2['vsiz']);
                    $len = \strlen($ret);
                    if ($len==0) {
                        $ret = false;
                        break;
                    }
                }
            }
        }
        return $ret;
    }

	public function loadData($id, $key)
	{
		if (!$this->usemem) return false;

		// 从后备中读取，没有的话会返回false
		$this->usemembak = true;
		$ret = $this->get($id, $key);
		if ($ret===false) {
			$ret2 = $this->isBakNull($id, $key);
			$this->usemembak = false;
			if (!$ret2) return false;
		}
		$this->usemembak = false;
		if ($ret!==false) {
			$ret2 = $this->set($id, $key, $ret);
			if (!$ret2) \common\Log::info('load_fail_memcachedihash', array('id'=>$id,'key'=>$key));
			return $ret;
		}

		// 从同步中读取，没有的话会返回false
		$this->usesyncmem = true;
		$ret = $this->get($id, $key);
		if ($ret===false) {
			$ret2 = $this->isSyncMemNull($id, $key);
			$this->usesyncmem = false;
			if (!$ret2) return false;
		}
		$this->usesyncmem = false;
		if ($ret!==false) {
			$ret2 = $this->set($id, $key, $ret);
			if (!$ret2) \common\Log::info('load_fail_memcachedihash2', array('id'=>$id,'key'=>$key));
			return $ret;
		}

		// 从硬盘中读取
		$this->usemem = false;
		$ret = $this->get($id, $key);
		$this->usemem = true;
		if ($ret!==false) {
			$ret2 = $this->set($id, $key, $ret);
			if (!$ret2) \common\Log::info('load_fail_memcachedihash3', array('id'=>$id,'key'=>$key));
		}
		return $ret;
	}
}

