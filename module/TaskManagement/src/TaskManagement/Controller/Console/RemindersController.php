<?php

namespace TaskManagement\Controller\Console;

use Zend\Mvc\Controller\AbstractConsoleController;


class RemindersController extends AbstractConsoleController {
	/**
	 * @param array $data
	 * @return \Zend\Stdlib\ResponseInterface
	 */
	public function sendAction($data=null)
	{
		var_dump('SEND ACTION');
	}
	
}