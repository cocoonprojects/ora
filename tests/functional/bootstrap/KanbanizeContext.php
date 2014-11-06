<?php

use Ora\Kanbanize\KanbanizeService;
use Ora\Kanbanize\KanbanizeTask;

include 'RestContext.php';

chdir(dirname(__DIR__));

$path = __DIR__ . '/../../../src/vendor/zendframework/zendframework/library';
putenv("ZF2_PATH=".$path);

include __DIR__ . '/../../../src/init_autoloader.php';

class KanbanizeContext extends RestContext {
	
	/** @var \Zend\Mvc\Application */
	private static $zendApp;
	private static $serviceManager;
	
	/** @BeforeSuite */
	static public function initializeZendFramework() {
		if(self::$zendApp === null) {
			$path = __DIR__.'/../../../src/config/application.config.php';
			self::$zendApp = Zend\Mvc\Application::init(include $path);
		}
		self::$serviceManager = self::$zendApp->getServiceManager();
        self::$serviceManager->get('ModuleManager')->loadModules();
        self::$serviceManager->setAllowOverride(true);
		self::$serviceManager->setService('TaskManagement\Service\Kanbanize', self::getMockService());
	}

	private static function getMockService() {
		return new MockService();
	}

	/**
	 * @Given /^that I want to use "([^"]*)" as Kanbanize Service$/
	 */
	public function thatIWantToUseAsKanbanizeService($serviceName)
	{
		$service = self::getMockService();
		switch($serviceName) {
			case "AlreadyInDestination":
				$service = new AlreadyInDestination();
				break;
			case "CannotAccept":
				$service = new CannotAccept();
				break;
			case "CannotMoveToOngoing":
				$service = new CannotMoveToOngoing();
				break;
			default:
				break;
		}
		self::$serviceManager->setService('TaskManagement\Service\Kanbanize', $service);
	}
	
}

class MockService implements KanbanizeService {

	public function createNewTask($projectId, $taskSubject, $boardId) {}
	
	public function deleteTask(KanbanizeTask $kanbanizeTask) {}
	
	public function getTasks($boardId, $status = null) {}
	
	public function acceptTask(KanbanizeTask $kanbanizeTask) {}
	
	public function moveBackToOngoing(KanbanizeTask $kanbanizeTask) {}
	
	public function listAvailableKanbanizeTasks() {}
	
	public function moveToCompleted(KanbanizeTask $kanbanizeTask) {}
}

class AlreadyInDestination extends MockService {
	public function acceptTask(KanbanizeTask $kanbanizeTask) {
		throw new AlreadyInDestinationException();
	}
}

class CannotAccept extends MockService {
	public function acceptTask(KanbanizeTask $kanbanizeTask) {
		throw new IllegalRemoteStateException();
	}
}

class CannotMoveToOngoing extends MockService {
	public function moveBackToOngoing(KanbanizeTask $kanbanizeTask) {
		throw new IllegalRemoteStateException();
	}	
}