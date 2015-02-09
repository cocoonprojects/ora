<?php
use Ora\Kanbanize\KanbanizeAPI;
use Ora\Kanbanize\ReadModel\KanbanizeTask;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;

class KanbanizeContext extends RestContext {

	private static $api;
	
	/**
	 *  @BeforeSuite
	 */
	public static function setupKanbanize(BeforeSuiteScope $scope){
		$config = file_exists('../src/config/autoload/kanbanize.local.php') ? include '../src/config/autoload/kanbanize.local.php' : include '../src/config/autoload/kanbanize.global.php';
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
