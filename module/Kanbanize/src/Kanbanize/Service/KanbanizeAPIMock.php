<?php

namespace Kanbanize\Service;

use Kanbanize\KanbanizeTask;

class KanbanizeAPIMock extends KanbanizeAPI
{
	private $values;
	
	private $tasks;
	
	public function __construct($tasks = array(), $values = array())
	{
		$this->tasks = $tasks;
		$this->values = $values;
	}
	
	public function createNewTask($boardid, $data = array())
	{
		return isset($values['createNewTask']) ? $values['createNewTask'] : uniqid();
	}
	
	public function moveTask($boardid, $taskid, $column, $options = array())
	{
		return isset($values['moveTask']) ? $values['moveTask'] : 1;
	}
	
	public function deleteTask($boardid, $taskid)
	{
		return [];
	}
	
	public function getAllTasks($boardid, $options = array())
	{
		return array_values($this->tasks);
	}
	
	public function getTaskDetails($boardid, $taskid, $options = array ())
	{
		if(isset($this->tasks[(string) $taskid])) {
			return $this->tasks[(string) $taskid];
		}
		throw new KanbanizeApiException('No Kanbanize task available with id ' . $taskid);
	}
}