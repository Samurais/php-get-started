<?php

namespace framework\helper;

class AppTTServerOpClass {
	private static $uidconverttype = 'default';
	private static $logpath = false;
	private static $dolog = false;
	private static $dologper = false;
	private static $instance = array ();
	private $conf;
	private $switch = false;
	private $auto = false;
	private $usemem = false;
	private $usemembak = false;
	private $usesyncmem = false;
	static function setUidConvertType($newtype) {
		switch ($newtype) {
			case 'default' :
			case 'crc32' :
				self::$uidconverttype = $newtype;
				return true;
				break;
			default :
				self::throwexception ( 'not support converttype ' . $newtype );
				return false;
				break;
		}
		return false;
	}
	static function setLogPerf($logopperf) {
		self::$dologper = $logopperf;
	}
	static function setLogPath($logpath) {
		if (! is_dir ( dirname ( $logpath ) ))
			return;
		self::$logpath = $logpath;
		self::$dolog = true;
	}
	static function writelog($tip, $param) {
		if (false == self::$logpath)
			return;
		error_log ( date ( 'Y-m-d H:i:s' ) . "\t" . $tip . "\t" . json_encode ( $param ) . "\n", 3, self::$logpath );
	}
	static function throwexception($msg) {
		throw new \Exception ( $msg );
	}
	static function getConf($userId, $group = 'main') {
		if (! isset ( $GLOBALS ['ttserver_cfg_' . $group] ))
			return false;
		$hashmax = $GLOBALS ['ttserver_cfg_' . $group] ['hash_index'] ['max'];
		$maxpower = isset ( $GLOBALS ['ttserver_cfg_' . $group] ['hash_index'] ['maxpower'] ) ? $GLOBALS ['ttserver_cfg_' . $group] ['hash_index'] ['maxpower'] : false;
		$hashiter = $GLOBALS ['ttserver_cfg_' . $group] ['hash_index'] ['iter'];
		$iterpower = isset ( $GLOBALS ['ttserver_cfg_' . $group] ['hash_index'] ['iterpower'] ) ? $GLOBALS ['ttserver_cfg_' . $group] ['hash_index'] ['iterpower'] : false;
		switch (self::$uidconverttype) {
			case 'default' :
				$hashIndex = ($maxpower !== false) ? ($userId & ($hashmax - 1)) : ($userId % $hashmax);
				break;
			case 'crc32' :
				$t = sprintf ( '%u', crc32 ( $userId ) >> 16 & 0xffff );
				$hashIndex = ($maxpower !== false) ? ($t & ($hashmax - 1)) : ($t % $hashmax);
				break;
		}
		$index = ($iterpower !== false) ? ($hashIndex >> $iterpower) : ( int ) ($hashIndex / $hashiter);
		if (! isset ( $GLOBALS ['ttserver_cfg_' . $group] ['hash_index'] ['index'] [$index] ))
			return false;
		$index2 = $GLOBALS ['ttserver_cfg_' . $group] ['hash_index'] ['index'] [$index];
		if (! isset ( $GLOBALS ['ttserver_cfg_' . $group] ['hash_db'] [$index2] ))
			return false;
		return $GLOBALS ['ttserver_cfg_' . $group] ['hash_db'] [$index2];
	}
	static function getSwitch($group = 'main') {
		if (! isset ( $GLOBALS ['ttserver_cfg_' . $group] ))
			return false;
		return $GLOBALS ['ttserver_cfg_' . $group] ['switch_db'];
	}
	public function __construct($conf, $switch) {
		$this->conf = $conf;
		$this->switch = $switch;
	}
	public function isNull($id, $key = '') {
		if (defined ( 'NO_MEMCACHED' ))
			return true;
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		$code = $mc->getResultCode ();
		if ($code == \Memcached::RES_NOTFOUND)
			return true;
		self::writelog ( 'isnull', array (
				'id' => $id,
				'code' => $code,
				'key' => $key 
		) );
		self::throwexception ( '1111isnull id [' . $id . '],code [' . $code . '],key [' . $key . ']' );
		// if ($code == \Memcached::RES_SUCCESS) return true;
		return false;
	}
	public function useDb($rev = array()) {
		if (empty ( $rev )) {
			$ret = array (
					$this->usemem,
					$this->usemembak,
					$this->usesyncmem 
			);
			
			$this->usemem = false;
			$this->usemembak = false;
			$this->usesyncmem = false;
			
			return $ret;
		} else {
			list ( $this->usemem, $this->usemembak, $this->usesyncmem ) = $rev;
			return;
		}
	}
	public function isLoadNull($id, $key = '') {
		if (defined ( 'NO_MEMCACHED' ))
			return true;
		if ($this->usemem) {
			$this->usemem = false;
			if (false == ($mc = $this->getMc ( $id )))
				return false;
			$this->usemem = true;
		} else {
			if (false == ($mc = $this->getMc ( $id )))
				return false;
		}
		$code = $mc->getResultCode ();
		if ($code == \Memcached::RES_NOTFOUND)
			return true;
		if ($code == \Memcached::RES_SUCCESS)
			return true;
		self::writelog ( 'isnullload', array (
				'id' => $id,
				'code' => $code,
				'key' => $key 
		) );
		self::throwexception ( '1111isnullload id [' . $id . '],code [' . $code . '],key [' . $key . ']' );
		return false;
	}
	private function isBakNull($id, $key = '') {
		if (defined ( 'NO_MEMCACHED' ))
			return true;
		if (false == ($mc = $this->getMc ( $id )))
			return true;
		$code = $mc->getResultCode ();
		if ($code == \Memcached::RES_NOTFOUND)
			return true;
		self::writelog ( 'isbaknull', array (
				'id' => $id,
				'code' => $code,
				'key' => $key 
		) );
		self::throwexception ( '1111isbaknull id [' . $id . '],code [' . $code . '],key [' . $key . ']' );
		// if ($code == \Memcached::RES_SUCCESS) return true;
		return false;
	}
	private function isSyncMemNull($id, $key = '') {
		if (defined ( 'NO_MEMCACHED' ))
			return true;
		if (false == ($mc = $this->getMc ( $id )))
			return true;
		$code = $mc->getResultCode ();
		if ($code == \Memcached::RES_NOTFOUND)
			return true;
		self::writelog ( 'issyncnull', array (
				'id' => $id,
				'code' => $code,
				'key' => $key 
		) );
		self::throwexception ( '1111issyncnull id [' . $id . '],code [' . $code . '],key [' . $key . ']' );
		// if ($code == \Memcached::RES_SUCCESS) return true;
		return false;
	}
	public function useMem() {
		$this->usemem = true;
	}
	private function getMemConf($id) {
		$conf = $this->conf;
		$memidx = $this->switch ['memidx'];
		if (0 == $memidx)
			return $conf;
		if (! isset ( $conf ['mem' . $memidx] )) {
			self::throwexception ( 'no config' );
			return $conf;
		}
		$count = count ( $conf ['mem' . $memidx] );
		if ($count == 0) {
			self::throwexception ( 'config error' );
			return $conf;
		}
		if ($count == 1)
			return $conf ['mem' . $memidx] [0];
		
		$id = ( int ) $id;
		$serverid = $id % $count;
		return $conf ['mem' . $memidx] [$serverid];
	}
	private function getMemBakConf($id) {
		$conf = $this->conf;
		if (0 == $this->switch ['memidx'])
			return false;
		$memidx = ($this->switch ['memidx'] == 1) ? 2 : 1;
		if ($this->switch ['membak'] == false)
			return false;
		if (! isset ( $conf ['mem' . $memidx] )) {
			self::throwexception ( 'no bak config' );
			return false;
		}
		$count = count ( $conf ['mem' . $memidx] );
		if ($count == 0) {
			self::throwexception ( 'bakconfig error' );
			return false;
		}
		if ($count == 1)
			return $conf ['mem' . $memidx] [0];
		
		$id = ( int ) $id;
		$serverid = $id % $count;
		return $conf ['mem' . $memidx] [$serverid];
	}
	private function getSyncMemConf($id) {
		$conf = $this->conf;
		if ($this->switch ['syncmem'] == false)
			return false;
		if (! isset ( $conf ['syncmem'] )) {
			self::throwexception ( 'no syncconfig' );
			return false;
		}
		$count = count ( $conf ['syncmem'] );
		if ($count == 0) {
			self::throwexception ( 'syncconfig error' );
			return false;
		}
		if ($count == 1)
			return $conf ['syncmem'] [0];
		
		$id = ( int ) $id;
		$serverid = $id % $count;
		return $conf ['syncmem'] [$serverid];
	}
	private function getMc($id) {
		if ($this->usemembak) {
			$conf = $this->getMemBakConf ( $id );
			if (! $conf)
				return false;
		} else if ($this->usesyncmem) {
			$conf = $this->getSyncMemConf ( $id );
			if (! $conf)
				return false;
		} else {
			if ($this->usemem) {
				$conf = $this->getMemConf ( $id );
			} else {
				$conf = $this->conf;
			}
		}
		if (self::$dolog)
			self::writelog ( 'getMc_conf', array (
					'conf' => json_encode ( $conf ) 
			) );
		return $this->_getMc ( $conf );
	}
	private function _getMc($conf) {
		if (self::$dolog)
			self::writelog ( 'getMc_start', array (
					'conf' => json_encode ( $conf ) 
			) );
		$host = $conf ['host'] . '_' . $conf ['port'];
		if (isset ( self::$instance [$host] )) {
			if (! self::$instance [$host] ['enable'])
				return false;
			if (is_object ( self::$instance [$host] ['mc'] ))
				return self::$instance [$host] ['mc'];
		}
		
		if (defined ( 'NO_MEMCACHED' )) {
			self::$instance [$host] ['mc'] = new Memcache ();
		} else {
			if (self::$dolog)
				self::writelog ( 'getMc_conn', array (
						'conf' => json_encode ( $conf ) 
				) );
			self::$instance [$host] ['mc'] = new \Memcached ();
			if (! isset ( $conf ['auto'] ) || $conf ['auto'] == 0) {
				self::$instance [$host] ['mc']->setOption ( \Memcached::OPT_COMPRESSION, false );
				// self::$instance[$host]['mc']->setOption(\Memcached::OPT_SERIALIZER,
			// \Memcached::SERIALIZER_IGBINARY);
			} else {
				$this->auto = true;
			}
		}
		self::$instance [$host] ['enable'] = false;
		if ($conf ['enable']) {
			if (@self::$instance [$host] ['mc']->addServer ( $conf ['host'], $conf ['port'] ))
				self::$instance [$host] ['enable'] = true;
		}
		
		if (! self::$instance [$host] ['enable'])
			return false;
		if (self::$dolog)
			self::writelog ( 'getMc_end', array (
					'conf' => json_encode ( $conf ) 
			) );
		return self::$instance [$host] ['mc'];
	}
	public function nget($id, $key, &$cas_token = null) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		self::writelog ( 'memcachedihash', array (
				'name' => 'get',
				'key' => $key,
				"usemem" => $this->usemem,
				"usemembak" => $this->usemembak,
				"usesyncmem" => $this->usesyncmem 
		) );
		if (self::$dologper)
			$start_time = microtime ( true );
		$data = $mc->get ( $key, null, $cas_token );
		if (self::$dologper) {
			$end_time = microtime ( true );
			$elapse = $end_time - $start_time;
			self::writelog ( 'prof_tc', array (
					'act' => 'get',
					'elapse' => $elapse,
					'key' => $key 
			) );
		}
		return $data;
	}
	public function delete($id, $key) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		$ret = $mc->delete ( $key );
		return $ret;
	}
	public function ngetMulti($id, $keys) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		if (self::$dolog)
			self::writelog ( 'memcachedihash', array (
					'name' => 'get',
					'key' => implode ( ",", array_values ( $keys ) ) 
			) );
		if (self::$dologper)
			$start_time = microtime ( true );
		$data = $mc->getMulti ( $keys );
		if (self::$dologper) {
			$end_time = microtime ( true );
			$elapse = $end_time - $start_time;
			self::writelog ( 'prof_tc', array (
					'act' => 'get',
					'elapse' => $elapse,
					'keys' => json_encode ( $keys ) 
			) );
		}
		return $data;
	}
	public function nset($id, $key, $data) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		if (self::$dolog)
			self::writelog ( 'memcachedihash', array (
					'name' => 'set',
					'key' => $key 
			) );
		if (self::$dologper)
			$start_time = microtime ( true );
		$ret = $mc->set ( $key, $data );
		if (self::$dologper) {
			$end_time = microtime ( true );
			$elapse = $end_time - $start_time;
			self::writelog ( 'prof_tc', array (
					'act' => 'set',
					'elapse' => $elapse,
					'key' => $key 
			) );
		}
		return $ret;
	}
	public function increment($id, $key, $value = 1) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		if (\defined ( "LOG_QUERY" ))
			\common\Log::info ( 'memcachedihash', array (
					'name' => 'increment',
					'key' => $key 
			) );
		if (\defined ( 'PROFILE_TC' ) && PROFILE_TC) {
			$start_time = \common\Utils::getMicroTime ();
		}
		$ret = $mc->increment ( $key, $value );
		if (\defined ( 'PROFILE_TC' ) && PROFILE_TC) {
			$end_time = \common\Utils::getMicroTime ();
			$elapse = $end_time - $start_time;
			\common\Log::info ( 'prof_tc', array (
					'act' => 'increment',
					'elapse' => $elapse,
					'key' => $key 
			) );
		}
		return $ret;
	}
	public function nadd($id, $key, $data) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		if (self::$dolog)
			self::writelog ( 'memcachedihash', array (
					'name' => 'add',
					'key' => $key 
			) );
		if (self::$dologper)
			$start_time = microtime ( true );
		$ret = $mc->add ( $key, $data );
		if (self::$dologper) {
			$end_time = microtime ( true );
			$elapse = $end_time - $start_time;
			self::writelog ( 'prof_tc', array (
					'act' => 'add',
					'elapse' => $elapse,
					'key' => $key 
			) );
		}
		return $ret;
	}
	public function ncas($id, $key, $data, $cas) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		if (self::$dolog)
			self::writelog ( 'memcachedihash', array (
					'name' => 'cas',
					'key' => $key 
			) );
		if (self::$dologper)
			$start_time = microtime ( true );
		$ret = $mc->cas ( $cas, $key, $data );
		if (self::$dologper) {
			$end_time = microtime ( true );
			$elapse = $end_time - $start_time;
			self::writelog ( 'prof_tc', array (
					'act' => 'cas',
					'elapse' => $elapse,
					'key' => $key 
			) );
		}
		return $ret;
	}
	public function cas($id, $key, $data, $cas) {
		return $this->ncas ( $id, $key, $data, $cas );
	}
	public function nsetMulti($id, $keys) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		if (self::$dologper)
			$start_time = microtime ( true );
		$ret = $mc->setMulti ( $keys );
		if (self::$dologper) {
			$end_time = microtime ( true );
			$elapse = $end_time - $start_time;
			self::writelog ( 'prof_tc', array (
					'act' => 'set',
					'elapse' => $elapse,
					'keys' => json_decode ( $keys ) 
			) );
		}
		if (self::$dolog)
			self::writelog ( 'memcachedihash_multi', array (
					'ret' => $ret,
					'name' => 'setmulti',
					'key' => json_encode ( array_keys ( $keys ) ) 
			), true );
		return $ret;
	}
	private function nreplace($id, $key, $data) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		if (self::$dolog)
			self::writelog ( 'memcachedihash', array (
					'name' => 'replace',
					'key' => $key 
			) );
		if (self::$dologper)
			$start_time = microtime ( true );
		$ret = $mc->replace ( $key, $data );
		if (self::$dologper) {
			$end_time = microtime ( true );
			$elapse = $end_time - $start_time;
			self::writelog ( 'prof_tc', array (
					'act' => 'replace',
					'elapse' => $elapse,
					'key' => $key 
			) );
		}
		return $ret;
	}
	
	// ֻ���л�ʱʹ��
	private function ndecrement($id, $key) {
		if (false == ($mc = $this->getMc ( $id )))
			return false;
		if (self::$dolog)
			self::writelog ( 'memcachedihash', array (
					'name' => 'decrement',
					'key' => $key 
			) );
		$ret = $mc->decrement ( $key );
		if (! $ret)
			self::writelog ( 'decrement_fail_memcachedihash', array (
					'id' => $id,
					'key' => $key 
			) );
		return true;
	}
	public function nload($id, $key, &$token = null) {
		if (! $this->usemem)
			return false;
			
			// �Ӻ��ж�ȡ��û�еĻ��᷵��false
		$this->usemembak = true;
		$ret = $this->nget ( $id, $key );
		if ($ret === false) {
			$ret2 = $this->isBakNull ( $id, $key );
			$this->usemembak = false;
			if (! $ret2)
				return false;
		}
		$this->usemembak = false;
		if ($ret !== false) {
			$ret2 = $this->nreplace ( $id, $key, $ret );
			if (! $ret2)
				self::writelog ( 'load_fail_memcachedihash', array (
						'id' => $id,
						'key' => $key 
				) );
			if ($ret2) {
				if (defined ( 'SWITCHTT' ) && SWITCHTT) {
					$this->usemembak = true;
					$this->ndecrement ( $id, $key );
					$this->usemembak = false;
				}
			}
			$token = strlen ( $ret );
			return $ret;
		}
		
		if (defined ( 'SWITCHTT' ) && SWITCHTT) {
			self::throwexception ( '1111switch id [' . $id . ']' );
			return false;
		}
		
		// ��ͬ���ж�ȡ��û�еĻ��᷵��false
		$this->usesyncmem = true;
		$ret = $this->nget ( $id, $key );
		if ($ret === false) {
			$ret2 = $this->isSyncMemNull ( $id, $key );
			$this->usesyncmem = false;
			if (! $ret2)
				return false;
		}
		$this->usesyncmem = false;
		if ($ret !== false) {
			$ret2 = $this->nreplace ( $id, $key, $ret );
			if (! $ret2)
				self::writelog ( 'load_fail_memcachedihash2', array (
						'id' => $id,
						'key' => $key 
				) );
			$token = strlen ( $ret );
			return $ret;
		}
		
		// ��Ӳ���ж�ȡ
		$this->usemem = false;
		$p = strpos ( $key, '__' );
		if ($p) {
			$prevkey = substr ( $key, 0, $p );
			$nextkey = substr ( $key, $p + 2 );
			$ret = $this->nget ( $id, $prevkey );
			if ($ret !== false) {
				$len = strlen ( $ret );
				while ( 1 ) {
					$ret2 = unpack ( "Nksiz/Nvsiz", $ret );
					if ($len < $ret2 ['ksiz'] + $ret2 ['vsiz'] + 8) {
						self::writelog ( 'unpackerror', array (
								'id' => $id,
								'key' => $key 
						) );
						self::throwexception ( '1111unpackerror id [' . $id . ']' );
						break;
					}
					$tmpkey = substr ( $ret, 8, $ret2 ['ksiz'] );
					if ($tmpkey == $nextkey) {
						$data = substr ( $ret, 8 + $ret2 ['ksiz'], $ret2 ['vsiz'] );
						$ret = $data;
						break;
						// if ($compress) $data = gzuncompress($data);
						// if ($dimnum==2) {
						// $ret = kl_unpackObjArray($keynames, $data);
						// } else {
						// $ret = kl_unpackObj($keynames, $data);
						// }
						// break;
					}
					$ret = substr ( $ret, 8 + $ret2 ['ksiz'] + $ret2 ['vsiz'] );
					$len = strlen ( $ret );
					if ($len == 0) {
						$ret = false;
						break;
					}
				}
			}
		}
		$this->usemem = true;
		if ($ret !== false) {
			$ret2 = $this->nreplace ( $id, $key, $ret );
			if (! $ret2)
				self::writelog ( 'load_fail_memcachedihash3', array (
						'id' => $id,
						'key' => $key 
				) );
		}
		$token = strlen ( $ret );
		return $ret;
	}
	public function loadData($id, $key) {
		if (! $this->usemem)
			return false;
			
			// �Ӻ��ж�ȡ��û�еĻ��᷵��false
		$this->usemembak = true;
		$ret = $this->get ( $id, $key );
		if ($ret === false) {
			$ret2 = $this->isBakNull ( $id, $key );
			$this->usemembak = false;
			if (! $ret2)
				return false;
		}
		$this->usemembak = false;
		if ($ret !== false) {
			$ret2 = $this->set ( $id, $key, $ret );
			if (! $ret2)
				self::writelog ( 'load_fail_memcachedihash', array (
						'id' => $id,
						'key' => $key 
				) );
			return $ret;
		}
		
		// ��ͬ���ж�ȡ��û�еĻ��᷵��false
		$this->usesyncmem = true;
		$ret = $this->get ( $id, $key );
		if ($ret === false) {
			$ret2 = $this->isSyncMemNull ( $id, $key );
			$this->usesyncmem = false;
			if (! $ret2)
				return false;
		}
		$this->usesyncmem = false;
		if ($ret !== false) {
			$ret2 = $this->set ( $id, $key, $ret );
			if (! $ret2)
				self::writelog ( 'load_fail_memcachedihash2', array (
						'id' => $id,
						'key' => $key 
				) );
			return $ret;
		}
		
		// ��Ӳ���ж�ȡ
		$this->usemem = false;
		$ret = $this->get ( $id, $key );
		$this->usemem = true;
		if ($ret !== false) {
			$ret2 = $this->set ( $id, $key, $ret );
			if (! $ret2)
				self::writelog ( 'load_fail_memcachedihash3', array (
						'id' => $id,
						'key' => $key 
				) );
		}
		return $ret;
	}
	
	/**
	 * 从硬盘中读取
	 */
	public function loadFromDb($id, $key) {
		// 从硬盘中读取
		$this->usemem = false;
		$p =\strpos ( $key, '__' );
		if ($p) {
			$prevkey =\substr ( $key, 0, $p );
			$nextkey =\substr ( $key, $p + 2 );
			$ret = $this->nget ( $id, $prevkey );
			if ($ret !== false) {
				$len =\strlen ( $ret );
				while ( 1 ) {
					$ret2 =\unpack ( "Nksiz/Nvsiz", $ret );
					if ($len < $ret2 ['ksiz'] + $ret2 ['vsiz'] + 8) {
						if (\defined ( "LOG_QUERY" ))
							\common\Log::info ( 'unpackerror', array (
									'id' => $id,
									'key' => $key 
							) );
						throw new \common\GameException ( '1111unpackerror id [' . $id . ']' );
						break;
					}
					$tmpkey =\substr ( $ret, 8, $ret2 ['ksiz'] );
					if ($tmpkey == $nextkey) {
						$data =\substr ( $ret, 8 + $ret2 ['ksiz'], $ret2 ['vsiz'] );
						$ret = $data;
						break;
					}
					$ret =\substr ( $ret, 8 + $ret2 ['ksiz'] + $ret2 ['vsiz'] );
					$len =\strlen ( $ret );
					if ($len == 0) {
						$ret = false;
						break;
					}
				}
			}
		}
		return $ret;
	}
	
	/**
	 * ��Ӳ����ȡ���
	 */
	public function loadDesk($id, $key) {
		$this->usemem = false;
		$this->usemembak = false;
		$this->usesyncmem = false;
		$p = strpos ( $key, '__' );
		if ($p) {
			$prevkey = substr ( $key, 0, $p );
			$nextkey = substr ( $key, $p + 2 );
			$ret = $this->nget ( $id, $prevkey );
			if ($ret !== false) {
				$len = strlen ( $ret );
				while ( 1 ) {
					$ret2 = unpack ( "Nksiz/Nvsiz", $ret );
					if ($len < $ret2 ['ksiz'] + $ret2 ['vsiz'] + 8) {
						return false;
						break;
					}
					$tmpkey = substr ( $ret, 8, $ret2 ['ksiz'] );
					if ($tmpkey == $nextkey) {
						$data = substr ( $ret, 8 + $ret2 ['ksiz'], $ret2 ['vsiz'] );
						$ret = $data;
						break;
					}
					$ret = substr ( $ret, 8 + $ret2 ['ksiz'] + $ret2 ['vsiz'] );
					$len = strlen ( $ret );
					if ($len == 0) {
						$ret = false;
						break;
					}
				}
			}
		}
		return $ret;
	}
}
