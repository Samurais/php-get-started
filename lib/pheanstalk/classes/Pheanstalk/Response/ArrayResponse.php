<?php

/**
 * A response with an ArrayObject interface to key=>value data
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Response_ArrayResponse
	implements Pheanstalk_Response
{
	private $_name;
	private $_data;

	/**
	 * @param string $name
	 * @param array $data
	 */
	public function __construct($name, $data)
	{
		$this->_name = $name;
		$this->_data = (array)$data;
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Response::getResponseName()
	 */
	public function getResponseName()
	{
		return $this->_name;
	}

	/**
	 * Object property access to ArrayObject data.
	 */
	public function __get($property)
	{
		$key = $this->_transformPropertyName($property);
		return isset($this->_data[$key]) ? $this->_data[$key] : null;
	}

	/**
	 * Object property access to ArrayObject data.
	 */
	public function __isset($property)
	{
		$key = $this->_transformPropertyName($property);
		return isset($this->_data[$key]);
	}

	public function getAll()
	{
		return $this->_data;
	}
	
	// ----------------------------------------

	/**
	 * Tranform underscored property name to hyphenated array key.
	 * @param string
	 * @return string
	 */
	private function _transformPropertyName($propertyName)
	{
		return str_replace('_', '-', $propertyName);
	}
}
