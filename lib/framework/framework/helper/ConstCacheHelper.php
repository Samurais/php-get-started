<?php
namespace framework\helper;

use \ConstCache;

/**
 * ConstCache数据处理类
 * 
 * @author zivn
 * @package framework\helper
 */
class ConstCacheHelper
{
	/**
     * ConstCache对象
     *
     * @var \ConstCache
     */
    private $constCache;
    
	/** 
	 * 构造函数
	 * 
	 * @param bool $enable
	 */
	public function __construct($enable)
	{
		if ($enable)
		{
			$this->constCache = new \ConstCache();
		}
	}
    
    /**
     * 启用缓存
     * 
     */
    function enable()
    {
    	if (empty($this->constCache))
    	{
    		$this->constCache = new \ConstCache();
    	}
    }
    
    /**
     * 禁用缓存
     * 
     */
    function disable()
    {
    	if (!empty($this->constCache))
    	{
    		$this->constCache = null;
    	}
    }

    /**
     * 取得ConstCache对象
     * 
	 * @return \ConstCache
	 */
	function getConstCache() 
	{
		return $this->constCache;
	}
    
    /**
	 * 添加新数据（如存在则失败）
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @return bool
     */
    public function add($key, $value)
    {
    	if (\defined("LOG_QUERY")) \common\Log::info('const_add', array('key'=>$key,"value"=>$value));
        return $this->constCache ? $this->constCache->add($key, $value) : false;
    }
    
    /**
     * 获取指定键名的数据
	 * 
	 * @param string $key
	 * @return mixed
     */
    public function get($key)
    {
        if (\defined("LOG_QUERY")) \common\Log::info('const_get', array('key'=>$key));
    	return $this->constCache ? $this->constCache->get($key) : null;
    }
    
    /**
     * 无效化所有缓存数据（清空缓存）
     * 
     * @return bool
     */
    public function flush()
    {
        if (\defined("LOG_QUERY")) \common\Log::info('const_flush', array());
    	return $this->constCache ? $this->constCache->flush() : false;
    }
    
    /**
     * 删除已创建的共享内存，更新配置时使用
     * 
     * @return bool
     */
    public function destroy()
    {
        if (\defined("LOG_QUERY")) \common\Log::info('const_destroy', array());
    	return $this->constCache ? $this->constCache->destroy() : false;
    }
}
?>
