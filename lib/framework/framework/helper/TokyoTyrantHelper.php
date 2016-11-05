<?php
namespace framework\helper;

use \TokyoTyrant;

/**
 * TokyoTyrant数据处理类
 * 
 * @author zivn
 * @package framework\helper
 */
class TokyoTyrantHelper
{
	/**
	 * 压缩的临界长度
	 * 
	 * @var int
	 */
	const COMPRESS_LENGTH = 200;
	/**
	 * 数据未压缩标志
	 * 
	 * @var string
	 */
	const FLAG_UNCOMPRESS = '0';
	/**
	 * 数据压缩标志
	 * 
	 * @var string
	 */
	const FLAG_COMPRESS = '1';
	
	/**
     * TokyoTyrant对象
     *
     * @var \TokyoTyrant
     */
    private $tokyoTyrant;
    
    /**
     * 存储前的预处理
     * 
     * @param mixed $value
     * @return mixed
     */
    private static function pack($value) 
	{
		$value = serialize($value);
		$length = strlen($value);
		
		if ($length > self::COMPRESS_LENGTH)
		{
			return self::FLAG_COMPRESS.gzcompress($value);
		}
		else
		{
			return self::FLAG_UNCOMPRESS.$value;
		}
	}
	
	/**
	 * 读取后的后续处理
	 * 
	 * @param mixed $value
     * @return mixed
	 */
	private static function unpack($value) 
	{
		$flag = substr($value, 0, 1);
		$value = substr($value, 1);
		
		if ($flag == self::FLAG_COMPRESS)
		{
			return unserialize(gzuncompress($value));
		}
		else 
		{
			return unserialize($value);
		}
	}

    /**
     * 取得TokyoTyrant对象
     * 
	 * @return \TokyoTyrant
	 */
	function getTokyoTyrant() 
	{
		return $this->tokyoTyrant;
	}

	/**
	 * 设置TokyoTyrant对象
	 * 
	 * @param \TokyoTyrant $tokyoTyrant
	 */
	function setTokyoTyrant($tokyoTyrant) 
	{
		$this->tokyoTyrant = $tokyoTyrant;
	}
    
	/**
	 * 存储数据（如存在则覆盖）
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
    public function add($key, $value)
    {
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('tt_add', array('key'=>$key,"value"=>$value));
    	$this->tokyoTyrant->put($key, self::pack($value));
    }
    
	/**
	 * 存储数据序列（如存在则覆盖）
	 * 
	 * @param array $items
	 */
    public function addMulti($items)
    {
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('tt_addMulti', array($items));
        $this->tokyoTyrant->put(array_map('self::pack', $items));
    }
    
	/**
	 * 获取指定键名的数据
	 * 
	 * @param string $key
	 * @return mixed
	 */
    public function get($key)
    {        
        $value = $this->tokyoTyrant->get($key);
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('tt_get', array('key'=>$key));
        return empty($value) ? null : self::unpack($value);
    }
    
	/**
	 * 获取指定键名序列的数据
	 * 
	 * @param array $keys
	 * @return array
	 */
    public function getMulti($keys)
    {        
        $values = $this->tokyoTyrant->get($keys);
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('tt_getMulti', array('key'=>$keys));
        return empty($values) ? null : array_map('self::unpack', $values);
    }
    
	/**
	 * 删除指定键名的数据
	 * 
	 * @param string $key
	 */
    public function delete($key)
    {
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('tt_delete', array('key'=>$key));
    	$this->tokyoTyrant->out($key);
    }
    
	/**
	 * 删除指定键名序列的数据
	 * 
	 * @param array $keys
	 */
    public function deleteMulti($keys)
    {
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('tt_deleteMulti', array('key'=>$keys));
    	$this->tokyoTyrant->out($keys);
    }
    
	/**
	 * 增加指定键名整数数据的值
	 * 
	 * @param string $key
	 * @param int $offset
	 * @return int
	 */
    public function increment($key, $offset=1)
    {
         if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('tt_increment', array('key'=>$keys));
        return $this->tokyoTyrant->add($key, $offset);
    }
    
	/**
	 * 清除所有数据（慎用）
	 * 
	 */
    public function flush()
    {
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('tt_flush', array());
        $this->tokyoTyrant->vanish();
    }
    
	/**
	 * 获取服务器统计信息
	 * 
	 * @return array
	 */
    public function stat()
    {        
        return $this->tokyoTyrant->stat();
    }

    public function nget($id, $key, $keynames, $dimnum, $compress)
    {
        if (false == ($mc = $this->tokyoTyrant)) return false;
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('memcachedihash', array('name'=>'get','key'=>$key));

        if (isset($GLOBALS['API_CFG']['profile_tc']) && $GLOBALS['API_CFG']['profile_tc']['enable']==true) {
            $time_start = microtime_float();
        }
        $data = $mc->get($key);
        if (isset($GLOBALS['API_CFG']['profile_tc']) && $GLOBALS['API_CFG']['profile_tc']['enable']==true) {
            $time_end = microtime_float();
            $elapse = $time_end - $time_start;
            if ($elapse>$GLOBALS['API_CFG']['profile_tc']['threshold']) \common\Log::info('prof_tc', array('act'=>'get','elapse'=>$elapse,'key'=>$key));
        }

        if ($data===false) return false;
        if ($compress) $data = gzuncompress($data);
        if ($dimnum!=0) {
            if ($dimnum==2) {
                $data = kl_unpackObjArray($keynames, $data);
            } else {
                $data = kl_unpackObj($keynames, $data);
            }
        }
        return $data;
    }

    public function ngetMulti($id, $keys)
    {
        if (false == ($mc = $this->tokyoTyrant)) return false;
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('memcachedihash', array('name'=>'get','key'=>implode(",", array_values($keys))));

        if (isset($GLOBALS['API_CFG']['profile_tc']) && $GLOBALS['API_CFG']['profile_tc']['enable']==true) {
            $time_start = microtime_float();
        }
        $data = $mc->getMulti($keys);
        if (isset($GLOBALS['API_CFG']['profile_tc']) && $GLOBALS['API_CFG']['profile_tc']['enable']==true) {
            $time_end = microtime_float();
            $elapse = $time_end - $time_start;
            if ($elapse>$GLOBALS['API_CFG']['profile_tc']['threshold']) \common\Log::info('prof_tc', array('act'=>'getMulti','elapse'=>$elapse,'keylen'=>strlen($keys)));
        }
        if ($data===false) return false;
        $rows = array();
        foreach($keys AS $k=>$key) {
            if (isset($data[$key])) {
                $ret = $data[$key];
                $arr = explode("#", $k);
                $fieldnames = $arr[1];
                $dimnum = $arr[2];
                if ($dimnum == 2) {
                    $ret = gzuncompress($ret);
                    $ret = kl_unpackObjArray($fieldnames, $ret);
                } else {
                    $ret = kl_unpackObj($fieldnames, $ret);
                }
                $rows[$key] = $ret;
            }
        }
        return $rows;
    }

    public function nset($id, $key, $val, $keytypes, $dimnum, $compress)
    {
        if (false == ($mc = $this->tokyoTyrant)) return false;
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('memcachedihash', array('name'=>'set','key'=>$key));

        if ($dimnum==2) {
            $data = kl_packObjArray($keytypes, $val);
        } else {
            $data = kl_packObj($keytypes, $val);
        }
        if ($compress) $data = gzcompress($data, 3);

        if (isset($GLOBALS['API_CFG']['profile_tc']) && $GLOBALS['API_CFG']['profile_tc']['enable']==true) {
            $time_start = microtime_float();
        }
        $ret = $mc->set($key, $data);
        if (isset($GLOBALS['API_CFG']['profile_tc']) && $GLOBALS['API_CFG']['profile_tc']['enable']==true) {
            $time_end = microtime_float();
            $elapse = $time_end - $time_start;
            if ($elapse>$GLOBALS['API_CFG']['profile_tc']['threshold']) \common\Log::info('prof_tc', array('act'=>'set','elapse'=>$elapse,'key'=>$key));
        }
        return $ret;
    }

    private function nreplace($id, $key, $val, $keytypes, $dimnum, $compress)
    {
        if (false == ($mc = $this->tokyoTyrant)) return false;
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('memcachedihash', array('name'=>'replace','key'=>$key));

        if ($dimnum==2) {
            $data = kl_packObjArray($keytypes, $val);
        } else {
            $data = kl_packObj($keytypes, $val);
        }
        if ($compress) $data = gzcompress($data, 3);

        if (isset($GLOBALS['API_CFG']['profile_tc']) && $GLOBALS['API_CFG']['profile_tc']['enable']==true) {
            $time_start = microtime_float();
        }
        $ret = $mc->replace($key, $data);
        if (isset($GLOBALS['API_CFG']['profile_tc']) && $GLOBALS['API_CFG']['profile_tc']['enable']==true) {
            $time_end = microtime_float();
            $elapse = $time_end - $time_start;
            if ($elapse>$GLOBALS['API_CFG']['profile_tc']['threshold']) \common\Log::info('prof_tc', array('act'=>'replace','elapse'=>$elapse,'key'=>$key));
        }
        return $ret;
    }

	// 只限切换时使用
    private function ndecrement($id, $key)
    {
        if (false == ($mc = $this->tokyoTyrant)) return false;
        if ($GLOBALS['API_CFG']['LOG_QUERY']) \common\Log::info('memcachedihash', array('name'=>'decrement','key'=>$key));

        $ret = $mc->decrement($key);
        if (!$ret) \common\Log::info('decrement_fail_memcachedihash', array('id'=>$id,'key'=>$key));
        return true;
    }

    public function nload($id, $key, $keynames, $keytypes, $dimnum, $compress)
    {
        if (!$this->usemem) return false;

        // 从后备中读取，没有的话会返回false
        $this->usemembak = true;
        $ret = $this->nget($id, $key, $keynames, $dimnum, $compress);
        if ($ret===false) {
            $ret2 = $this->isBakNull($id, $key);
            $this->usemembak = false;
            if (!$ret2) return false;
        }
        $this->usemembak = false;
        if ($ret!==false) {
            $ret2 = $this->nreplace($id, $key, $ret, $keytypes, $dimnum, $compress);
            if (!$ret2) \common\Log::info('load_fail_memcachedihash', array('id'=>$id,'key'=>$key));
        	if ($ret2) {
		        if (isset($GLOBALS['API_CFG']['switchtt']) && $GLOBALS['API_CFG']['switchtt']==1) {
		        	$this->usemembak = true;
		        	$this->ndecrement($id, $key);
		        	$this->usemembak = false;
		        }
            }
            return $ret;
        }

    	if (isset($GLOBALS['API_CFG']['switchtt']) && $GLOBALS['API_CFG']['switchtt']==1) {
        	throw new cls_exception_support('switch id ['. $id. ']');
        	return false;
        }

        // 从同步中读取，没有的话会返回false
        $this->usesyncmem = true;
        $ret = $this->nget($id, $key, $keynames, $dimnum, $compress);
        if ($ret===false) {
            $ret2 = $this->isSyncMemNull($id, $key);
            $this->usesyncmem = false;
            if (!$ret2) return false;
        }
        $this->usesyncmem = false;
        if ($ret!==false) {
            $ret2 = $this->nreplace($id, $key, $ret, $keytypes, $dimnum, $compress);
            if (!$ret2) \common\Log::info('load_fail_memcachedihash2', array('id'=>$id,'key'=>$key));
            return $ret;
        }

        // 从硬盘中读取
        $this->usemem = false;
        $p = strpos($key, '__');
        if ($p) {
            $prevkey = substr($key, 0, $p);
            $nextkey = substr($key, $p+2);
            $ret = $this->nget($id, $prevkey, $keynames, 0, false);
            if ($ret!==false) {
                $len = strlen($ret);
                while (1) {
                    $ret2 = unpack("Nksiz/Nvsiz", $ret);
                    if ($len<$ret2['ksiz']+$ret2['vsiz']+8) {
                        \common\Log::info('unpackerror', array('id'=>$id,'key'=>$key));
                        throw new cls_exception_support('unpackerror id ['. $id. ']');
                        break;
                    }
                    $tmpkey = substr($ret, 8, $ret2['ksiz']);
                    if ($tmpkey==$nextkey) {
                        $data = substr($ret, 8+$ret2['ksiz'], $ret2['vsiz']);
                        if ($compress) $data = gzuncompress($data);
                        if ($dimnum==2) {
                            $ret = kl_unpackObjArray($keynames, $data);
                        } else {
                            $ret = kl_unpackObj($keynames, $data);
                        }
                        break;
                    }
                    $ret = substr($ret, 8+$ret2['ksiz']+$ret2['vsiz']);
                    $len = strlen($ret);
                    if ($len==0) {
                        $ret = false;
                        break;
                    }
                }
            }
        } else {
            $ret = $this->nget($id, $key, $keynames, $dimnum, $compress);
        }
        $this->usemem = true;
        if ($ret!==false) {
            $ret2 = $this->nreplace($id, $key, $ret, $keytypes, $dimnum, $compress);
            if (!$ret2) \common\Log::info('load_fail_memcachedihash3', array('id'=>$id,'key'=>$key));
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
?>
