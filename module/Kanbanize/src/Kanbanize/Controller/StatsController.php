<?php

namespace Kanbanize\Controller;

use People\Entity\Organization;
use TaskManagement\Entity\Task;
use TaskManagement\Entity\Stream;
use Application\Entity\User;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class StatsController extends AbstractActionController
{
	private $em;

	public function __construct($em)
	{
		$this->em = $em;
	}

	public function statsAction()
	{
		$qb = $this->em
			 	   ->createQueryBuilder();

		$orgCount = $qb->select('count(organization.id)')
					   ->from(Organization::class, 'organization')
					   ->getQuery()
					   ->getSingleScalarResult();

		$taskCount = $qb->select('count(task.id)')
					   ->from(Task::class, 'task')
					   ->getQuery()
					   ->getSingleScalarResult();

		$usersCount = $qb->select('count(user.id)')
					   ->from(User::class, 'user')
					   ->getQuery()
					   ->getSingleScalarResult();

		$kanbanCount = $qb->select('count(o.id)')
					   ->from(Organization::class, 'o')
					   ->where('o.settings LIKE :api')
					   ->setParameter('api', '%apiKey%')
					   ->getQuery()
					   ->getSingleScalarResult();

		$data = [
			'orgs' => $orgCount,
			'tasks' => $taskCount,
			'users' => $usersCount,
			'kanbanize' => $kanbanCount
		];

		$view = new ViewModel(['stats' => $data]);
        $view->setTemplate('stats.phtml');

        return $view;
	}
}