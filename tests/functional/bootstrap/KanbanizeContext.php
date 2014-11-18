<?php

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\RawMinkContext;
use Ora\Kanbanize\KanbanizeAPI;
use Ora\Kanbanize\KanbanizeTask;

class KanbanizeContext extends RawMinkContext implements Context {

	private static $api;
	
	/**
	 *  @BeforeSuite
	 */
	public static function setupTasksOnKanbanize(){
		$config = include '../src/config/autoload/kanbanize.local.php';
		$api = new KanbanizeAPI();
		$api->setApiKey($config['kanbanize']['apikey']);
		$api->setUrl($config['kanbanize']['url']);
		$api->moveTask('3','114',KanbanizeTask::COLUMN_ACCEPTED);
		$api->moveTask('3','115',KanbanizeTask::COLUMN_COMPLETED);
		$api->moveTask('3','116',KanbanizeTask::COLUMN_ONGOING);
		$api->moveTask('3','117',KanbanizeTask::COLUMN_COMPLETED);
		$api->moveTask('3','119',KanbanizeTask::COLUMN_COMPLETED);
		$api->moveTask('3','120',KanbanizeTask::COLUMN_ACCEPTED);
		$api->moveTask('3','118',KanbanizeTask::COLUMN_ONGOING);
	}
}
