<?php 

namespace Kanbanize\Controller;

use Application\Controller\OrganizationAwareController;
use Kanbanize\Service\KanbanizeService;
use People\Service\OrganizationService;
use Kanbanize\Service\ImportDirector;
use Zend\View\Model\JsonModel;
use TaskManagement\Service\NotificationService;

class ImportsController extends OrganizationAwareController{

	protected static $collectionOptions = ['POST'];
	protected static $resourceOptions = [];

	/**
	 * @var KanbanizeService
	 */
	private $kanbanizeService;

	/**
	 * @var KanbanizeImporter
	 */
	private $kanbanizeImporter;

	/**
	 * @var NotificationService
	 */
	private $notificationService;

	public function __construct(OrganizationService $orgService, ImportDirector $importDirector, NotificationService $notificationService){
		parent::__construct($orgService);
		$this->kanbanizeImporter = $importDirector;
		$this->notificationService = $notificationService;
	}

	public function create($data){

		if(is_null($this->identity())) {
			$this->response->setStatusCode(401);
			return $this->response;
		}

		if(!$this->isAllowed($this->identity(), $this->organization, 'Kanbanize.Task.Import')) {
			$this->response->setStatusCode(403);
			return $this->response;
		}

		$organization = $this->getOrganizationService()->getOrganization($this->organization->getId());
		$importResult = $this->kanbanizeImporter->import($organization, $this->identity());
		$this->notificationService->sendKanbanizeImportResultMail($importResult, $this->organization);
		$this->response->setStatusCode(200);
		return $this->response;
	}

	public function getKanbanizeImporter(){
		return $this->kanbanizeImporter;
	}

	public function getNotificationService(){
		return $this->notificationService;
	}
	protected function getCollectionOptions() {
		return self::$collectionOptions;
	}

	protected function getResourceOptions() {
		return self::$resourceOptions;
	}
}