<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Ora\ReadModel\Task;
use Ora\ReadModel\Estimation;
use Ora\ReadModel\TaskMember;
use Zend\Mvc\Controller\Plugin\Url;
use Ora\User\User;
use BjyAuthorize\Service\Authorize;

class TaskJsonModel extends JsonModel
{
	/**
	 * 
	 * @var Url
	 */
	private $url;
	/**
	 * 
	 * @var User
	 */
	private $user;
	
	/**
	 * 
	 * @var BjyAuthorize\Service\Authorize
	 */
	private $authorize;
	
	public function __construct(Url $url, User $user, Authorize $authorize) {
		$this->url = $url;
		$this->user = $user;
		$this->authorize = $authorize;
	}
	
	public function serialize()
	{
		$resource = $this->getVariable('resource');
	
		if(is_array($resource)) {
			$representation['tasks'] = [];
 			if ($this->authorize->isAllowed(NULL, 'TaskManagement.Task.create')) { 
				$representation['_links']['create'] = $this->url->fromRoute('tasks');
 			}
			foreach ($resource as $r) {
				$representation['tasks'][] = $this->serializeOne($r);
			}
		} else {
			$representation = $this->serializeOne($resource);
		}
		return Json::encode($representation);		
	}

	private function serializeOne(Task $task) {
		
		$links = [];
		
		if($this->authorize->isAllowed($task, 'TaskManagement.Task.showDetails')){
			$links['self'] = $this->url->fromRoute('tasks', ['id' => $task->getId()]);	
		}
		
		if($this->authorize->isAllowed($task, 'TaskManagement.Task.edit')){
			$links['edit'] = $this->url->fromRoute('tasks', ['id' => $task->getId()]);
		}
		
		if($this->authorize->isAllowed($task, 'TaskManagement.Task.delete')){					
			$links['delete'] = $this->url->fromRoute('tasks', ['id' => $task->getId()]);
		}		
		
		if($this->authorize->isAllowed($task, 'TaskManagement.Task.join')){
			$links['join'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'members']);
		}
		
		if ($this->authorize->isAllowed($task, 'TaskManagement.Task.unjoin')) {		 
			$links['unjoin'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'members']); 
		}	
			
		if ($this->authorize->isAllowed($task, 'TaskManagement.Task.estimate')) {	   
			$links['estimate'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'estimations']); 
		}
		
		if ($this->authorize->isAllowed($task, 'TaskManagement.Task.execute')) {
			$links['execute'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'transitions']);
		}

		if ($this->authorize->isAllowed($task, 'TaskManagement.Task.complete')) {
			$links['complete'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'transitions']);
		}
		
		if ($this->authorize->isAllowed($task, 'TaskManagement.Task.accept')) {
			$links['accept'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'transitions']);
		}
		
		if ($this->authorize->isAllowed($task, 'TaskManagement.Task.assignShares')) {
			$links['assignShares'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'shares']);
		}
		
		$rv = [
			'id' => $task->getId (),
			'subject' => $task->getSubject (),
			'createdAt' => date_format($task->getCreatedAt(), 'c'),
			'createdBy' => is_null ( $task->getCreatedBy () ) ? "" : $task->getCreatedBy ()->getFirstname () . " " . $task->getCreatedBy ()->getLastname (),
			'type' => $task->getType (),
			'status' => $task->getStatus(),
			'stream' => $this->getStream($task),
			'members' => $this->getMembersArray($task),
			'_links' => $links,
		];
		
		if($task->getStatus() >= Task::STATUS_ONGOING) {
			$rv['estimation'] = $task->getAverageEstimation();
		}

		return $rv;
	}
	
	private function getStream(Task $task) {
		$stream = $task->getStream();
		$rv['subject'] = $stream->getSubject();
		$rv['_links']['self'] = $this->url->fromRoute('streams', ['id' => $stream->getId()]);
		return $rv;
	}
	
	private function getMembersArray(Task $task){
		$members = array();
		foreach ($task->getMembers() as $tm) {
			$m = $this->serializeOneMember($tm);
			$members[$tm->getMember()->getId()] = $m;
		}
		return $members;
	}
	
	private function serializeOneMember(TaskMember $tm) {
		$member = $tm->getMember();
		$rv = [
				'firstname' => $member->getFirstname(),
				'lastname' => $member->getLastname(),
				'picture' => $member->getPicture(),
				'role' => $tm->getRole(),
				'_links' => [
						'self' => $this->url->fromRoute('users', ['id' => $member->getId()]),
				],
		];
		
		$rv['estimation'] = $this->getEstimation($tm);
		if($this->user->getId() != $member->getId() && isset($rv['estimation'])) {
			// others member estimation aren't exposed outside the system
			$rv['estimation']['value'] = -2;
		}
		
		if($tm->getShare() != null && $tm->getTask()->getStatus() >= Task::STATUS_CLOSED) {
			$rv['share'] = $tm->getShare();
			$rv['delta'] = $tm->getDelta();
		}
		foreach ($tm->getShares() as $key => $share) {
			$rv['shares'][$key] = array(
				'value' => $share->getValue(),
				'createdAt' => date_format($share->getCreatedAt(), 'c'),
			);
		}
		return $rv;
	}
	
	private function getEstimation(TaskMember $tm) {
		$estimation = $tm->getEstimation();
		return is_null($estimation->getValue()) ? null : [
			'value' => $estimation->getValue(),
			'createdAt' => date_format($estimation->getCreatedAt(), 'c'),
		];
	}	 
}