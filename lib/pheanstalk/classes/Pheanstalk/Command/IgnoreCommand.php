<?php

/**
 * The 'ignore' command.
 * Removes a tube from the watch list to reserve jobs from.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Command_IgnoreCommand
	extends Pheanstalk_Command_AbstractCommand
	implements Pheanstalk_ResponseParser
{
	private $_jobid;
	private $_aheadtime;
	private $_type;

	/**
	 * @param string $tube
	 */
	public function __construct($jobid, $aheadtime, $type)
	{
		$this->_jobid = $jobid;
		$this->_aheadtime = $aheadtime;
		$this->_type = $type;
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Command::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'ignore '.$this->_jobid.' '.$this->_aheadtime.' '.$this->_type;
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_ResponseParser::parseRespose()
	 */
	public function parseResponse($responseLine, $responseData)
	{
		if (preg_match('#^DELETED (\d+)$#', $responseLine, $matches))
		{
			return $this->_createResponse('DELETED', array(
				'id' => (int)$matches[1]
			));
		}
		elseif (preg_match('#^REDELAYED (\d+)$#', $responseLine, $matches))
		{
			return $this->_createResponse('REDELAYED', array(
				'id' => (int)$matches[1]
			));
		}
		elseif (preg_match('#^NOT_FOUND$#', $responseLine, $matches))
		{
			return $this->_createResponse('NOT_FOUND', array(
			));
		}
		elseif (preg_match('#^NOT_IGNORED$#', $responseLine, $matches))
		{
			return $this->_createResponse('NOT_IGNORED', array(
			));
		}
		elseif (preg_match('#^WATCHING (\d+)$#', $responseLine, $matches))
		{
			return $this->_createResponse('WATCHING', array(
				'count' => (int)$matches[1]
			));
		}
		else
		{
			throw new Pheanstalk_Exception('Unhandled response: '.$responseLine);
		}
	}
}
