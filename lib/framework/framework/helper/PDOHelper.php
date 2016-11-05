<?php
namespace framework\helper;

use \PDO;

/**
 * PDO数据处理类
 * 
 * @author pzd
 * @package framework\helper
 */
class PDOHelper
{
	/**
     * pdo对象
     *
     * @var \PDO
     */
    private $pdo;
    /**
     * 数据库名
     *
     * @var string
     */
    private $dbName;
    /**
     * 数据表名
     *
     * @var string
     */
    private $tableName;
    /**
     * 类名
     *
     * @var string
     */
    private $className;
    
	/** 
	 * 构造函数
	 * 
	 * @param string $className
	 * @param string $dbName
	 */
	public function __construct($className, $dbName=null)
	{
		$this->className = $className;
		
		if (!empty($dbName))
		{
			$this->dbName = $dbName;
		}
	}

	/**
	 * 取得库名
	 * 
	 * @return String
	 */
	function getDBName() 
	{
		return $this->dbName;
	}

	/**
	 * 设置库名
	 * 
	 * @param String $dbName
	 */
	function setDBName($dbName) 
	{
		$this->dbName = $dbName;
	}
    
	/**
	 * 取得表名
	 * 
	 * @return String
	 */
	function getTableName() 
	{
		if (empty($this->tableName))
		{
			$classRef = new \ReflectionClass($this->className);
			$this->tableName = $classRef->getConstant('TABLE_NAME');
		}
		
		return $this->tableName;
	}

	/**
	 * 取得类名
	 * 
	 * @return String
	 */
	function getClassName() 
	{
		return $this->className;
	}
    
	/**
	 * 取得查询表名
	 * 
	 * @return String
	 */
	function getLibName() 
	{
		return "`{$this->getDBName()}`.`{$this->getTableName()}`";
	}

    /**
     * 取得PDO对象
     * 
	 * @return \PDO 
	 */
	function getPdo() 
	{
		return $this->pdo;
	}
    
    /**
     * 设置PDO对象
     * 
     * @param \PDO $pdo
     */
    function setPdo($pdo)
    {
    	$this->pdo = $pdo;
    }

	/**
     * 添加一个对象到数据库
     * 
     * @param Object $entity
     * @param array $fields
     * @param string $onDuplicate
     * @return int
     */
    public function add($entity, $fields, $onDuplicate = null)
    {
        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);
        
    	$params = array();
        
        foreach ($fields as $field)
        {
            $params[$field] = addslashes($entity->$field);
        }
        $strValues = "'" . implode('\', \'', $params) . "'";
        
        
        $query = "INSERT INTO {$this->getLibName()} ({$strFields}) VALUES ({$strValues})";

        if (!empty($onDuplicate))
        {
        	$query .= 'ON DUPLICATE KEY UPDATE '. $onDuplicate;
        }
        
        $statement = $this->pdo->prepare($query);
//        $params = array();
//        
//        foreach ($fields as $field)
//        {
//            $params[$field] = addslashes($entity->$field);
//        }
        $statement->execute();
        if (\defined("LOG_QUERY")) \common\Log::info('db_add', array('query'=>$query));
        return $this->pdo->lastInsertId();
    }

	/**
	 * 扩展插入
	 *
	 * @param array $entitys
	 * @param array $fields
	 * @return bool
	 */
	public function addMulti($entitys, $fields)
	{
		$items = array();
        
        foreach ($entitys as $index => $entity)
        {
        	$params = array();
        	
	        foreach ($fields as $field)
	        {
	            $params[] = addslashes($entity->$field);
	        }
	        
	        $items[] = '(\'' . implode('\', \'', $params).'\')';
        }
        
        $query = "INSERT INTO {$this->getLibName()} (`" . implode('`,`', $fields) . "`) VALUES ".implode(',', $items);        
        $statement = $this->pdo->prepare($query);
        if (\defined("LOG_QUERY")) \common\Log::info('db_addMulti', array('query'=>$query));
        return $statement->execute();
	}
 
    /**
     * REPLACE模式添加一个对象到数据库
     * 
     * @param Object $entity
     * @param array $fields
     * @return int
     */
    public function replace($entity, $fields)
    {
        $strFields = '`' . implode('`,`', $fields) . '`';
        $strValues = ':' . implode(', :', $fields);
        
        $params = array();
        
        foreach ($fields as $field)
        {
            $params[$field] = addslashes($entity->$field);
        }
        $strValues = "'" . implode('\', \'', $params) . "'";
        
        $query = "REPLACE INTO {$this->getLibName()} ({$strFields}) VALUES ({$strValues})";
        $statement = $this->pdo->prepare($query);
//        $params = array();
//        
//        foreach ($fields as $field)
//        {
//            $params[$field] = $entity->$field;
//        }
        
        $statement->execute();
        if (\defined("LOG_QUERY")) \common\Log::info('db_replace', array('query'=>$query));
        return $this->pdo->lastInsertId();
    }
    
    public function replaceMulti($entitys, $fields)
    {
    	$items = array();
    
    	foreach ($entitys as $index => $entity)
    	{
    		$params = array();
    		 
    		foreach ($fields as $field)
    		{
    			$params[] = addslashes($entity->$field);
    		}
    		 
    		$items[] = '(\'' . implode('\', \'', $params).'\')';
    	}
    
    	$query = "REPLACE INTO {$this->getLibName()} (`" . implode('`,`', $fields) . "`) VALUES ".implode(',', $items);
    	$statement = $this->pdo->prepare($query);
    	if (\defined("LOG_QUERY")) \common\Log::info('db_addMulti', array('query'=>$query));
    	return $statement->execute();
    }
   
    /**
     * 更新所有符合条件的对象
     *
     * @param array $fields
     * @param array $params
     * @param string $where
     * @param bool $change
     * @return bool
     */
    public function update($fields, $params, $where, $change=false)
    {
        if ($change)
        {
            $updateFields = array_map(__CLASS__ . '::changeFieldMap', $fields);
        } 
        else 
        {
            $updateFields = array_map(__CLASS__ . '::updateFieldMap', $fields);
        }
        
        $strUpdateFields = implode(',', $updateFields);
        foreach ($params as $k => $v)
        {
        	$strUpdateFields = str_replace(":{$k}", "'".addslashes($v)."'", $strUpdateFields);
        	$where = str_replace(":{$k}", "'".addslashes($v)."'", $where);
        }
       
        $query = "UPDATE {$this->getLibName()} SET {$strUpdateFields} WHERE {$where}";
        $statement = $this->pdo->prepare($query);
        if (\defined("LOG_QUERY")) \common\Log::info('db_update', array('query'=>$query));
        return $statement->execute();
    }

    /**
     * 取得符合条件的第一条记录的第一个值
     *
     * @param string $where
     * @param array $params
     * @param string $fields
     * @return mixed
     */
    public function fetchValue($where = '1', $params = null, $fields = '*')
    {
    	if (is_array($params))
    	{
    		foreach ($params as $k => $v)
    		{
    			if (is_int($k))
    			{
    				$tmp = explode("?", $where, 2);
    				$where = implode("'".addslashes($v)."'", $tmp);
    			}
    			else
    			{
    				$where = str_replace(":{$k}", "'".addslashes($v)."'", $where);
    			}
    		}
    	}
    	
        $query = "SELECT {$fields} FROM {$this->getLibName()} WHERE {$where} limit 1";
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        if (\defined("LOG_QUERY")) \common\Log::info('db_fechValue', array('query'=>$query));
        return $statement->fetchColumn();
    }
    
    /**
     * 取得所有符合条件的数据（数组）
     * 
     * @param string $where
     * @param array $params
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    public function fetchArray($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null, $groupBy = null)
    {
    	if (is_array($params))
    	{
    		foreach ($params as $k => $v)
    		{
    			if (is_int($k))
    			{
    				$tmp = explode("?", $where, 2);
    				$where = implode("'".addslashes($v)."'", $tmp);
    			}
    			else
    			{
    				$where = str_replace(":{$k}", "'".addslashes($v)."'", $where);
    			}
    		}
    	}
    	
        $query = "SELECT {$fields} FROM {$this->getLibName()} WHERE {$where}";
        
        if ($groupBy)
        {
        	$query .= " GROUP BY {$groupBy}";
        }
        
        if ($orderBy)
        {
            $query .= " ORDER BY {$orderBy}";
        }
        
        if ($limit)
        {
            $query .= " limit $limit";
        }
        
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        if (\defined("LOG_QUERY")) \common\Log::info('db_fechArray', array('query'=>$query));
        return $statement->fetchAll();
    }
    
    /**
     * 获取所有符合条件的数据的第一列（一维数组）
     * 
     * @param string $where
     * @param array $params
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    public function fetchCol($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null)
    {
        $results = $this->fetchArray($where, $params, $fields, $orderBy, $limit);        
        return empty($results) ? array() : array_map('reset', $results);
    }

    /**
     * 取得所有符合条件的对象
     * 
     * @param string $where
     * @param array $params
     * @param string $fields
     * @param string $orderBy
     * @param string $limit
     * @return array
     */
    public function fetchAll($where = '1', $params = null, $fields = '*', $orderBy = null, $limit = null)
    {
    	if (is_array($params))
    	{
    		foreach ($params as $k => $v)
    		{
    			if (is_int($k))
    			{
    				$tmp = explode("?", $where, 2);
    				$where = implode("'".addslashes($v)."'", $tmp);
    			}
    			else
    			{
    				$where = str_replace(":{$k}", "'".addslashes($v)."'", $where);
    			}
    		}
    	}
    	
        $query = "SELECT {$fields} FROM {$this->getLibName()} WHERE {$where}";

        if ($orderBy)
        {
            $query .= " order by {$orderBy}";
        }
        
        if ($limit)
        {
            $query .= " limit {$limit}";
        }
        
        $statement = $this->pdo->prepare($query);

        if (!$statement->execute())
        {
        	throw new \Exception('data base error');
        }
        
        $statement->setFetchMode(PDO::FETCH_CLASS, $this->className);
        if (\defined("LOG_QUERY")) \common\Log::info('db_fechAll', array('query'=>$query));
        return $statement->fetchAll();
    }
    
    /**
     * 根据条件返回一个对象
     *
     * @param string $where
     * @param array $params
     * @param string $fields
     * @return object
     */
    public function fetchEntity($where = '1', $params = null, $fields = '*', $orderBy = null)
    {
    	if (is_array($params))
    	{
    		foreach ($params as $k => $v)
    		{
    			if (is_int($k))
    			{
    				$tmp = explode("?", $where, 2);
    				$where = implode("'".addslashes($v)."'", $tmp);
    			}
    			else
    			{
    				$where = str_replace(":{$k}", "'".addslashes($v)."'", $where);
    			}
    		}
    	}
    	
        $query = "SELECT {$fields} FROM {$this->getLibName()} WHERE {$where}";

        if ($orderBy)
        {
            $query .= " order by {$orderBy}";
        }
        
        $query .= " limit 1";  

        $statement = $this->pdo->prepare($query);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, $this->className);
        if (\defined("LOG_QUERY")) \common\Log::info('db_fetchEntity', array('query'=>$query));
        return $statement->fetch();
        
    }

    /**
     * 删除符合条件的记录
     *
     * @param string $where
     * @param array $params
     */
    public function remove($where, $params)
    {
    	if (is_array($params))
    	{
    		foreach ($params as $k => $v)
    		{
    			if (is_int($k))
    			{
    				$tmp = explode("?", $where, 2);
    				$where = implode("'".addslashes($v)."'", $tmp);
    			}
    			else
    			{
    				$where = str_replace(":{$k}", "'".addslashes($v)."'", $where);
    			}
    		}
    	}
    	
        if (empty($where))
        {
        	return false;
        }

        $query = "DELETE FROM {$this->getLibName()} WHERE {$where}";
        $statement = $this->pdo->prepare($query);
        if (\defined("LOG_QUERY")) \common\Log::info('db_remove', array('query'=>$query));
        return $statement->execute();
    }

    public static function updateFieldMap($field)
    {
        return '`' . $field . '`=:' . $field;
    }
    
    public static function changeFieldMap($field)
    {
        return '`' . $field . '`=`' . $field . '`+:' . $field;
    }
    
    public function queryExec($query, $return = true)
    {

    	//error_log("time: " . date('H:i:s') . ' : ' . $query . "\n", 3, '/data/wwwlogs/sql.log');
    	//$query = addslashes($query);
    	$statement = $this->pdo->prepare($query);
		$statement->execute();
		if($return)
		{
			$statement->setFetchMode(PDO::FETCH_ASSOC);
			if (\defined("LOG_QUERY")) \common\Log::info('db_fechArray', array('query'=>$query));
			return $statement->fetchAll();
		}
    } 
    
    public function insertId()
    {
    	return $this->pdo->lastInsertId();
    }
}
