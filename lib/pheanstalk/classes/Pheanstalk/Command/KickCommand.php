<?php

/**
 * The 'kick' command.
 * Kicks buried or delayed jobs into a 'ready' state.
 * If there are buried jobs, it will kick up to $max of them.
 * Otherwise, it will kick up to $max delayed jobs.
 *
 * @author Paul Annesley
 * @package Pheanstalk
 * @licence http://www.opensource.org/licenses/mit-license.php
 */
class Pheanstalk_Command_KickCommand
	extends Pheanstalk_Command_AbstractCommand
	implements Pheanstalk_ResponseParser
{
	private $_jobid;
	private $_delaytime;

	/**
	 * @param int $max The maximum number of jobs to kick
	 */
	public function __construct($jobid, $delaytime)
	{
		$this->_jobid = $jobid;
		$this->_delaytime = $delaytime;
	}

	/* (non-phpdoc)
	 * @see Pheanstalk_Command::getCommandLine()
	 */
	public function getCommandLine()
	{
		return 'kick '.$this->_jobid.' '.$this->_delaytime;
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
		else
		{
			throw new Pheanstalk_Exception('Unhandled response: '.$responseLine);
		}
	}
}
