<?php
namespace TaskManagement\View;

use Zend\View\Model\JsonModel;
use Zend\Json\Json;
use Zend\Permissions\Acl\Acl;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\Estimation;
use TaskManagement\Entity\TaskMember;
use Zend\Mvc\Controller\Plugin\Url;
use Application\Entity\User;

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
	 * @var Acl
	 */
	private $acl;
	
	public function __construct(Url $url, User $user, Acl $acl) {
		$this->url = $url;
		$this->user = $user;
		$this->acl = $acl;
	}
	
	public function serialize()
	{
		$resource = $this->getVariable('resource');
	
		if(is_array($resource)) {
			$hal['_links']['self']['href'] = $this->url->fromRoute('tasks');
			$hal['_embedded']['ora:task'] = array_map(array($this, 'serializeOne'), $resource);
			$hal['count'] = count($resource);
			$hal['total'] = count($resource);
		} else {
			$hal = $this->serializeOne($resource);
		}
		if ($this->acl->isAllowed($this->user, NULL, 'TaskManagement.Task.create')) {
			$hal['_links']['ora:create']['href'] = $this->url->fromRoute('tasks');
		}
		return Json::encode($hal);		
	}

	protected function serializeOne(Task $task) {
		
		$links = [];
		if($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.showDetails')){
			$links['self']['href'] = $this->url->fromRoute('tasks', ['id' => $task->getId()]);	
		}

		if($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.edit')){
			$links['ora:edit'] = $this->url->fromRoute('tasks', ['id' => $task->getId()]);
		}
		
		if($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.delete')){					
			$links['ora:delete'] = $this->url->fromRoute('tasks', ['id' => $task->getId()]);
		}		
		
		if($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.join')){
			$links['ora:join'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'members']);
		}
		
		if ($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.unjoin')) {		 
			$links['ora:unjoin'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'members']); 
		}	
			
		if ($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.estimate')) {	   
			$links['ora:estimate'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'estimations']); 
		}
		
		if ($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.execute')) {
			$links['ora:execute'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'transitions']);
		}

		if ($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.complete')) {
			$links['ora:complete'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'transitions']);
		}
		
		if ($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.accept')) {
			$links['ora:accept'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'transitions']);
		}
		
		if ($this->acl->isAllowed($this->user, $task, 'TaskManagement.Task.assignShares')) {
			$links['ora:assignShares'] = $this->url->fromRoute('tasks', ['id' => $task->getId(), 'controller' => 'shares']);
		}
		
		$rv = [
			'id' => $task->getId (),
			'subject' => $task->getSubject (),
			'createdAt' => date_format($task->getCreatedAt(), 'c'),
			'createdBy' => is_null ( $task->getCreatedBy () ) ? "" : $task->getCreatedBy ()->getFirstname () . " " . $task->getCreatedBy ()->getLastname (),
			'type' => $task->getType (),
			'status' => $task->getStatus(),
			'stream' => $this->getStream($task),
			'members' => array_map(array($this, 'serializeOneMember'), $task->getMembers()),
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
		$rv['_links']['self']['href'] = $this->url->fromRoute('streams', ['id' => $stream->getId()]);
		return $rv;
	}
	
	protected function serializeOneMember(TaskMember $tm) {
		$member = $tm->getMember();
		$rv = [
				'firstname' => $member->getFirstname(),
				'lastname' => $member->getLastname(),
				'picture' => $member->getPicture(),
				'role' => $tm->getRole(),
// 				'_links' => [
// 						'self' => $this->url->fromRoute('users', ['id' => $member->getId()]),
// 				],
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
		if(is_null($estimation) || is_null($estimation->getValue())) {
			return null;
		}
		return [
			'value' => $estimation->getValue(),
			'createdAt' => date_format($estimation->getCreatedAt(), 'c'),
		];
	}	 
}
