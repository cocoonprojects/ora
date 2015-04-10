<?php

namespace TaskManagement\Service;

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\Event;
use Doctrine\ORM\EntityManager;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Aggregate\AggregateRepository;
use Prooph\EventStore\Aggregate\AggregateType;
use Prooph\EventStore\Stream\SingleStreamStrategy;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Rhumsaa\Uuid\Uuid;
use Application\Entity\User;
use TaskManagement\Stream;
use TaskManagement\Task;
use TaskManagement\Entity\Task as ReadModelTask;
use Zend\View\Renderer\RendererInterface;

class EventSourcingTaskService extends AggregateRepository implements TaskService, EventManagerAwareInterface
{
	/**
	 * 
	 * @var EntityManager
	 */
	private $entityManager;
	/**
	 * 
	 * @var EventManagerInterface
	 */
	private $events;
    
    public function __construct(EventStore $eventStore, EntityManager $entityManager)
    {
		parent::__construct($eventStore, new AggregateTranslator(), new SingleStreamStrategy($eventStore), AggregateType::fromAggregateRootClass(Task::class));
		$this->entityManager = $entityManager;	
	}
	
	public function addTask(Task $task)
	{			
	    $task->setEventManager($this->getEventManager());
		$this->addAggregateRoot($task);
		return $task;
	}
	
	/**
	 * Retrieve task entity with specified ID
	 */
	public function getTask($id)
	{
		$tId = $id instanceof Uuid ? $id->toString() : $id;
		$task = $this->getAggregateRoot($tId);
		if($task != null) {
			$task->setEventManager($this->getEventManager());
		}
		return $task;
	}
	
	/**
	 * Get the list of all available tasks 
	 */
	public function findTasks()
	{
		$repository = $this->entityManager->getRepository(ReadModelTask::class);
	    return $repository->findBy(array(), array('mostRecentEditAt' => 'DESC'));	    
	}
	
	public function findTask($id) {
		return $this->entityManager->find(ReadModelTask::class, $id);
	}
	
	public function findStreamTasks($streamId) {	
		$repository = $this->entityManager->getRepository(ReadModelTask::class)->findBy(array('stream' => $streamId));
	    return $repository;
	}
	
	public function setEventManager(EventManagerInterface $events) {
		$events->setIdentifiers(array(			'TaskManagement\TaskService',
			__CLASS__,
			get_class($this)
		));
		$this->events = $events;
	}
	
	public function getEventManager()
	{
		if (!$this->events) {
			$this->setEventManager(new EventManager());
		}
		return $this->events;
	}
	
	public function getAcceptedTaskIdsToNotify(\DateInterval $timeboxForAcceptedTask){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t.id as TASK_ID')
			->from(ReadModelTask::class, 't')			
			->where('DATE_DIFF(CURRENT_DATE(), t.acceptedAt) = '.$timeboxForAcceptedTask->format('%d') . '-1')			
			->andWhere('t.status = :taskStatus')			
			->setParameter('taskStatus', Task::STATUS_ACCEPTED)			
			->getQuery();			
		
		return $query->getArrayResult();		
	}
	
	
	public function getAcceptedTaskIdsToClose(\DateInterval $timeboxForAcceptedTask){
		
		$builder = $this->entityManager->createQueryBuilder();
		$query = $builder->select('t.id as TASK_ID')
			->from(ReadModelTask::class, 't')			
			->where('DATE_DIFF(CURRENT_DATE(), t.acceptedAt) >= :interval')
			->andWhere('t.status = :taskStatus')			
			->setParameter('interval', $timeboxForAcceptedTask->format('%d'))
			->setParameter('taskStatus', Task::STATUS_ACCEPTED)
			->getQuery();			
			
		return $query->getArrayResult();		
	}
	
	public function notifyMembersForShareAssignment(Task $task, RendererInterface $renderer, $taskMembersWithEmptyShares){

		$result = false;
		
		foreach ($taskMembersWithEmptyShares as $taskMember){
			
			//invio mail
			$params = array(
				'name' => $taskMember->getFirstname()." ".$taskMember->getLastname(),
				'taskSubject' => $task->getSubject(),
				'taskId' => $task->getId(),
				'emailSubject' => "O.R.A. - your contribution is required!"
			);
			
			$content = $renderer->render('task-management/email_templates/hurryup-taskmember', $params);

			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: O.R.A. Team<orateam@ora.com>' . "\r\n";
			
			$result = mail($taskMember->getEmail(), $params['emailSubject'], $content, $headers, 'orateam@ora.com');
			
		}
		
		return $result;
	}
	
	/**
	 * Retrieve an array of members (Application\Entity\User) of $task that haven't assigned any share
	 * 
	 * @param Task $task
	 * @return array of Application\Entity\User or empty array
	 */
	public function findMembersWithEmptyShares(Task $task){
		
		$members = array();
		
		$readModelTask = $this->findTask($task->getId());
		$taskMembers = $readModelTask->getMembers();
		
		foreach($taskMembers as $taskMember){
			
			if(count($taskMember->getShare() == 0)){
				$members[] = $taskMember->getMember();
			}
		}
		
		return $members;
	}
}