<?php
namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Permissions\Acl\Acl;
use Application\Entity\User;
use People\Assertion\MemberOfOrganizationAssertion;
use Accounting\Assertion\AccountHolderAssertion;
use Accounting\Assertion\MemberOfOrganizationOrAccountHolderAssertion;
use TaskManagement\Assertion\OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion;
use TaskManagement\Assertion\MemberOfNotAcceptedTaskAssertion;
use TaskManagement\Assertion\TaskMemberNotOwnerAndNotCompletedTaskAssertion;
use TaskManagement\Assertion\TaskOwnerAndNotCompletedTaskAssertion;
use TaskManagement\Assertion\OwnerOfOpenOrCompletedTaskAssertion;
use TaskManagement\Assertion\TaskOwnerAndOngoingOrAcceptedTaskAssertion;
use TaskManagement\Assertion\TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion;
use TaskManagement\Assertion\TaskMemberAndAcceptedTaskAssertion;

class AclFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$config = $serviceLocator->get('Config');
		$env = getenv('APPLICATION_ENV') ? : "local";
		
		$acl = new Acl();
		$acl->addRole(User::ROLE_GUEST);
		$acl->addRole(User::ROLE_USER);
		$acl->addRole(User::ROLE_ADMIN, User::ROLE_USER);
		$acl->addRole(User::ROLE_SYSTEM);

		$acl->addResource('Ora\Organization');
		$acl->allow(User::ROLE_USER, 'Ora\Organization', array('People.Organization.userList', 'TaskManagement.Task.list', 'TaskManagement.Stream.list'), new MemberOfOrganizationAssertion());
		
		$acl->addResource('Ora\Account');
		$acl->addResource('Ora\OrganizationAccount');
		$acl->allow(User::ROLE_USER, 'Ora\Account','Accounting.Account.statement', new AccountHolderAssertion()); 
		$acl->allow(User::ROLE_USER, 'Ora\OrganizationAccount','Accounting.Account.statement', new MemberOfOrganizationOrAccountHolderAssertion());
		$acl->allow(User::ROLE_USER, "Ora\OrganizationAccount",'Accounting.Account.deposit', new AccountHolderAssertion()); 
		
		$acl->addResource('Ora\Task');
		$acl->allow(User::ROLE_USER, null, 'TaskManagement.Task.create');
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.showDetails', new \TaskManagement\Assertion\MemberOfOrganizationAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.join', new OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.estimate', new MemberOfNotAcceptedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.unjoin', new TaskMemberNotOwnerAndNotCompletedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', array('TaskManagement.Task.edit', 'TaskManagement.Task.delete'), new TaskOwnerAndNotCompletedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.execute', new OwnerOfOpenOrCompletedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.complete', new TaskOwnerAndOngoingOrAcceptedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.accept', new TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.assignShares', new TaskMemberAndAcceptedTaskAssertion());
		
		if($env == "production"){
			$acl->allow(User::ROLE_SYSTEM, null, array('TaskManagement.Task.closeTasksCollection', 'TaskManagement.Reminder.createReminder'));
		}else{
			$acl->allow(User::ROLE_ADMIN, null, array('TaskManagement.Task.closeTasksCollection', 'TaskManagement.Reminder.createReminder'));
		}
		
		return $acl;
	}
}