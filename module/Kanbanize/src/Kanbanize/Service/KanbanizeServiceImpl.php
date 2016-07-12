<?php

namespace Kanbanize\Service;

use Application\Entity\User;
use Doctrine\ORM\EntityManager;
use Kanbanize\KanbanizeTask;
use TaskManagement\Entity\Stream as ReadModelStream;
use Kanbanize\Entity\KanbanizeStream;
use Kanbanize\Entity\KanbanizeTask as ReadModelKanbanizeTask;
use People\Organization as WriteModelOrganization;
use People\Entity\Organization;

/**
 * Service Kanbanize
 *
 * @author Andrea Lupia <alupia@dimes.unical.it>
 *
 */
class KanbanizeServiceImpl implements KanbanizeService
{
	/**
	 *
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 *
	 * @var KanbanizeAPI
	 */
	private $kanbanize;

	/*
	 * Constructs service
	 */
	public function __construct(EntityManager $em, KanbanizeAPI $api) {
		$this->kanbanize = $api;
		$this->entityManager = $em;
	}

	public function getBoardActivities($boardId)
	{
		$now = new \DateTimeImmutable('now');
		$fromDate = $now->sub(new \DateInterval('P1D'))->format('Y-m-d');
		$toDate = $now->add(new \DateInterval('P1D'))->format('Y-m-d');

		$response = $this->kanbanize
						 ->getBoardActivities($boardId, $fromDate, $toDate);

		return $response;
	}

	public function blockTask($boardid, $taskid, $reason)
	{
		$response = $this->kanbanize
			 			 ->blockTask($boardid, $taskid, 'block', $reason);

		return $response;
	}

	public function moveTask(KanbanizeTask $task, $status) {
		$boardId = $task->getKanbanizeBoardId();
		$taskId = $task->getKanbanizeTaskId();
		$options = [];

		if ($task->getLaneName()) {
			$options['lane'] = $task->getLaneName();
		}

		$response = $this->kanbanize
						 ->moveTask($boardId, $taskId, $status, $options);

		if($response != 1) {
			throw new OperationFailedException('Unable to move the task ' + $taskId + ' in board ' + $boardId + 'to the column ' + $status + ' because of ' + $response);
		}

		return 1;
	}

	public function moveTaskonKanbanize(ReadModelKanbanizeTask $kanbanizeTask, $status,$boardId){

		$taskId = $kanbanizeTask->getTaskId();
		$options = [];

		if ($kanbanizeTask->getLaneName()) {
			$options['lane'] = $task->getLaneName();
		}

		$response = $this->kanbanize
						 ->moveTask($boardId, $taskId, $status, $options);

		// error_log(print_r($response, true));

		if($response != 1) {
			throw new OperationFailedException('Unable to move the task ' + $taskId + ' in board ' + $boardId + 'to the column ' + $status + ' because of ' + $response);
		}

		return 1;
	}


	public function createNewTask($taskSubject, $taskTitle, $boardId, $options) {
		$createdAt = new \DateTime ();

		// TODO: Modificare createdBy per inserire User
		$createdBy = "NOME UTENTE INVENTATO";

		$all_options = array_merge($options, [
			'title' => $taskTitle,
			'description' => $taskSubject,
		]);

		$id = $this->kanbanize->createNewTask ( $boardId, $all_options );

		if (is_null ( $id )) {
			throw OperationFailedException("Cannot create task on Kanbanize");
		}
		return $id;
	}

	public function deleteTask(KanbanizeTask $task) {
		$ans = $this->kanbanize->deleteTask($task->getKanbanizeBoardId(), $task->getKanbanizeTaskId());
		if (isset($ans["Error"]))
			throw new OperationFailedException($ans["Error"]);
		return $ans;
	}

	public function getTasks($boardId, $status = null) {
		$tasks_to_return = array ();
		$tasks = $this->kanbanize->getAllTasks ( $boardId );
		if (is_null ( $status ))
			return $tasks;
		else {
			foreach ( $tasks as $singletask ) {
				if ($singletask ["columnname"] == $status)
					$tasks_to_return [] = $singletask;
			}

			return $tasks_to_return;
		}
	}
	public function acceptTask(KanbanizeTask $task) {
		$info = $this->kanbanize->getTaskDetails($task->getKanbanizeBoardId(), $task->getKanbanizeTaskId());
		if(isset($info['Error'])) {
			throw new OperationFailedException($info["Error"]);
		}
		if ( $info['columnname'] == KanbanizeTask::COLUMN_ACCEPTED){
			return;
		}
		if($info['columnname'] == KanbanizeTask::COLUMN_COMPLETED){
			$this::moveTask($task, KanbanizeTask::COLUMN_ACCEPTED);
		}else{
			throw new IllegalRemoteStateException("Cannot accpet a task which is " + $info["columnname"]);
		}
	}
	public function executeTask(KanbanizeTask $task) {
		$info = $this->kanbanize->getTaskDetails($task->getKanbanizeBoardId(), $task->getKanbanizeTaskId());
		if(isset($info['Error'])) {
			throw new OperationFailedException($info["Error"]);
		}
		if($info["columnname"] == KanbanizeTask::COLUMN_ONGOING){
			return;
		}

		if($info['columnname'] == KanbanizeTask::COLUMN_COMPLETED || $info['columnname'] == KanbanizeTask::COLUMN_OPEN){
			$this::moveTask($task, KanbanizeTask::COLUMN_ONGOING);
		}else{
			throw new IllegalRemoteStateException("Cannot move task in ongoing from "+$info["columnname"]);
		}
	}

	public function completeTask(KanbanizeTask $task) {
		$info = $this->kanbanize->getTaskDetails($task->getKanbanizeBoardId(), $task->getKanbanizeTaskId());
		if(isset($info['Error'])) {
			throw new OperationFailedException($info["Error"]);
		}
		if($info["columnname"] == KanbanizeTask::COLUMN_COMPLETED){
			return;
		}
		if (in_array($info['columnname'], [KanbanizeTask::COLUMN_ONGOING, KanbanizeTask::COLUMN_ACCEPTED])) {
			$this->moveTask($task, KanbanizeTask::COLUMN_COMPLETED);
		} else {
			throw new IllegalRemoteStateException("Cannot move task in completed from "+$info["columnname"]);
		}
	}

	public function closeTask(KanbanizeTask $task) {
		// TODO: To be implemented
	}
	/**
	 * (non-PHPdoc)
	 * @see \Kanbanize\Service\KanbanizeService::findStreamByBoardId()
	 */
	public function findStreamByBoardId($boardId, $organization){
		switch (get_class($organization)){
			case Organization::class :
			case WriteModelOrganization::class:
				$organizationId = $organization->getId();
				break;
			case Uuid::class:
				$organizationId = $organization->toString();
				break;
			default :
				$organizationId = $organization;
		}
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select ( 's' )
			->from(KanbanizeStream::class, 's')
			->where('s.organization = :organization')
			->andWhere('s.boardId = :boardId')
			->setParameter ( ':organization', $organizationId )
			->setParameter ( ':boardId', $boardId );
		return $query->getQuery()->getOneOrNullResult();
	}
	/**
	 * (non-PHPdoc)
	 * @see \Kanbanize\Service\KanbanizeService::findStreamByBoardId()
	 */
	public function findStreamByProjectId($projectId, $organization){
		$test = 'test';
		try {
		switch (get_class($organization)){
			case Organization::class :
			case WriteModelOrganization::class:
				$organizationId = $organization->getId();
				break;
			case Uuid::class:
				$organizationId = $organization->toString();
				break;
			default :
				$organizationId = $organization;
		}
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select ( 's' )
			->from(KanbanizeStream::class, 's')
			->where('s.organization = :organization')
			->andWhere('s.projectId = :projectId')
			->setParameter(':organization', $organizationId)
			->setParameter(':projectId', $projectId);
		$test = $query->getQuery()->getOneOrNullResult();
	} catch (\Exception $e) {
		var_dump('Eccezione: '.$e->getTraceAsString());
	}
		return $test;
	}

	public function findStreamByOrganization($organization){

		switch (get_class($organization)){
			case Organization::class :
			case WriteModelOrganization::class:
				$organizationId = $organization->getId();
				break;
			case Uuid::class:
				$organizationId = $organization->toString();
				break;
			default :
				$organizationId = $organization;
		}

		$builder = $this->entityManager->createQueryBuilder();

		$query = $builder->select ( 's' )
			->from(ReadModelStream::class, 's')
			->where('s.organization = :organization')
			->orderBy('s.createdAt', 'DESC')
			->setMaxResults(1)
			->setParameter(':organization', $organizationId);

		return $query->getQuery()->getOneOrNullResult();
	}

	/**
	 * (non-PHPdoc)
	 * @see \Kanbanize\Service\KanbanizeService::findTask()
	 */
	public function findTask($taskId, $organization){
		switch (get_class($organization)){
			case Organization::class :
			case WriteModelOrganization::class:
				$organizationId = $organization->getId();
				break;
			case Uuid::class:
				$organizationId = $organization->toString();
				break;
			default :
				$organizationId = $organization;
		}
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t')
			->from(ReadModelKanbanizeTask::class, 't')
			->innerjoin('t.stream', 's', 'WITH', 's.organization = :organization')
			->where('t.taskId = :taskId')
			->setParameter(':organization', $organizationId)
			->setParameter(':taskId', $taskId);
		return $query->getQuery()->getOneOrNullResult();
	}

	public function initApi($apiKey, $subdomain){
		if(is_null($apiKey)){
			throw new KanbanizeApiException("Cannot connect to Kanbanize due to missing api key");
		}
		if(is_null($subdomain)){
			throw new KanbanizeApiException("Cannot connect to Kanbanize due to missing account subdomain");
		}
		$this->kanbanize->setApiKey($apiKey);
		$this->kanbanize->setUrl(sprintf(Importer::API_URL_FORMAT, $subdomain));
	}
}