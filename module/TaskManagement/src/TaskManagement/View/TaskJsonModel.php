<?php
namespace TaskManagement\View;

use People\Entity\Organization;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\TaskMember;
use TaskManagement\TaskInterface;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractController;
use Zend\View\Model\JsonModel;

class TaskJsonModel extends JsonModel
{
	/**
	 * @var Organization
	 */
	private $organization;
	/**
	 * @var AbstractController
	 */
	private $controller;

	public function __construct(AbstractController $controller, Organization $organization = null) {
		$this->controller = $controller;
		$this->organization = $organization;
	}
	
	public function serialize()
	{
		$resource = $this->getVariable('resource');

		if(is_array($resource)) {
			$hal['_links']['self']['href'] = $this->controller->url()->fromRoute('tasks', ['orgId' => $this->organization->getId()]);
			if ($this->controller->isAllowed($this->controller->identity(), NULL, 'TaskManagement.Task.create')) {
				$hal['_links']['ora:create']['href'] = $this->controller->url()->fromRoute('tasks', ['orgId' => $this->organization->getId()]);
			}
			$hal['_embedded']['ora:task'] = array_map(array($this, 'serializeOne'), $resource);
			$hal['count'] = count($resource);
			$hal['total'] = $this->getVariable('totalTasks');
			if($hal['count'] < $hal['total']){
				$hal['_links']['next']['href'] = $this->controller->url()->fromRoute('tasks', ['orgId' => $this->organization->getId()]);
			}
			if(!is_null($this->getVariable('stats'))){
				$hal['_embedded']['ora:task']['stats'] = $this->getVariable('stats');
			}

		} else {
			$hal = $this->serializeOne($resource);
			if ($this->controller->isAllowed($this->controller->identity(), NULL, 'TaskManagement.Task.create')) {
				$hal['_links']['ora:create']['href'] = $this->controller->url()->fromRoute('tasks', ['orgId' => $resource->getOrganizationId()]);
			}
		}
		return Json::encode($hal);
	}
	protected function serializeOne(TaskInterface $task) {
		$links = [ ];
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.get' )) {
			$links ['self'] ['href'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId () 
			] );
		}
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.edit' )) {
			$links ['ora:edit'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId () 
			] );
		}
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.delete' )) {
			$links ['ora:delete'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId () 
			] );
		}
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.join' )) {
			$links ['ora:join'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId (),
					'controller' => 'members' 
			] );
		}
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.unjoin' )) {
			$links ['ora:unjoin'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId (),
					'controller' => 'members' 
			] );
		}
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.estimate' )) {
			$links ['ora:estimate'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId (),
					'controller' => 'estimations' 
			] );
		}
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.execute' )) {
			$links ['ora:execute'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId (),
					'controller' => 'transitions' 
			] );
		}
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.complete' )) {
			$links ['ora:complete'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId (),
					'controller' => 'transitions' 
			] );
		}
		
		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.accept' )) {
			$links ['ora:accept'] = $this->controller->url ()->fromRoute ( 'tasks', [ 
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId (),
					'controller' => 'transitions' 
			] );
		}

		if ($this->controller->isAllowed ( $this->controller->identity (), $task, 'TaskManagement.Task.assignShares' )) {
			$links ['ora:assignShares'] = $this->controller->url ()->fromRoute ( 'tasks', [
					'id' => $task->getId (),
					'orgId' => $task->getOrganizationId (),
					'controller' => 'shares'
			] );
		}
		
		if($this->controller->isAllowed($this->controller->identity(), $task,'TaskManagement.Reminder.add-estimation')){
			$links['ora:remindEstimation'] = $this->controller->url()->fromRoute('tasks', [
				'id' => $task->getId (),
				'orgId' => $task->getOrganizationId (),
				'controller' => 'reminders',
				'type'=>'add-estimation'
			]);
		}
		
		$rv = [
			'id' => $task->getId (),
			'subject' => $task->getSubject(),
			'createdAt' => date_format($task->getCreatedAt(), 'c'),
			'createdBy' => is_null ( $task->getCreatedBy () ) ? "" : $task->getCreatedBy ()->getFirstname () . " " . $task->getCreatedBy ()->getLastname (),
			'type' => $task->getType (),
			'status' => $task->getStatus(),
			'stream' => $this->getStream($task),
			'members' => array_map([$this, 'serializeOneMember'], $task->getMembers()),
		];
		
		if ($task->getStatus () >= Task::STATUS_ONGOING) {
			$rv ['estimation'] = $task->getAverageEstimation ();
		}
		if ($task->getStatus () >= Task::STATUS_ACCEPTED) {
			$rv ['acceptedAt'] = is_null ( $task->getAcceptedAt () ) ? null : date_format ( $task->getAcceptedAt (), 'c' );
			$rv ['daysRemainingToAssignShares'] = $this->getDaysLeftForAssignShares ( $task );
		}
		$rv ['_links'] = $links;
		return $rv;
	}
	
	private function getStream(TaskInterface $task) {
		$rv['id'] = $task->getStreamId();
		if($task instanceof Task) {
			$rv['subject'] = $task->getStream()->getSubject();	// temporary backward compatibility
		}
		$rv['_links']['self']['href'] = $this->controller->url()->fromRoute('streams', ['id' => $task->getStreamId(), 'orgId' => $task->getOrganizationId()]);
		return $rv;
	}
	
	protected function serializeOneMember($tm) {
		if($tm instanceof TaskMember) {
			$member = $tm->getMember();
			$rv = [
				'id' => $member->getId(),
				'firstname' => $member->getFirstname(),
				'lastname' => $member->getLastname(),
				'picture' => $member->getPicture(),
				'role' => $tm->getRole(),
				'createdAt' => date_format($tm->getCreatedAt(), 'c')
			];
			if(!(is_null($tm->getEstimation()) || is_null($tm->getEstimation()->getValue()))) {
				$rv['estimation'] = $tm->getEstimation()->getValue();
				$rv['estimatedAt'] = date_format($tm->getEstimation()->getCreatedAt(), 'c');
			}

			if($tm->getShare() != null && $tm->getTask()->getStatus() >= Task::STATUS_CLOSED) {
				$rv['share'] = $tm->getShare();
				$rv['delta'] = $tm->getDelta();
				$rv['credits'] = $tm->getCredits();
			}
			foreach ($tm->getShares() as $key => $share) {
				$rv['shares'][$key] = array(
					'value' => $share->getValue(),
					'createdAt' => date_format($share->getCreatedAt(), 'c'),
				);
			}
		} else {
			$rv = $tm;	// Copy the array
			foreach($rv as $key => $value) {
				if($value instanceof \DateTime) {
					$rv[$key] = date_format($value, 'c');
				}
			}
		}

		if($this->controller->identity()->getId() != $rv['id'] && isset($rv['estimation'])) {
			// others member estimation aren't exposed outside the system
			$rv['estimation'] = -2;
		}

 		$rv['_links'] = [
// 			'self' => $this->controller->url()->fromRoute('users', ['id' => $member->getId()]),
		];
		return $rv;
	}

	private function getDaysLeftForAssignShares($task) {
		if($task->getSharesAssignmentExpiresAt() != null){
			return date_diff($task->getSharesAssignmentExpiresAt(), new \DateTime())->format('%d');
		}
		
		return null;
	}
}
