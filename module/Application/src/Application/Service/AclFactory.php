<?php
namespace Application\Service;

use Accounting\Assertion\AccountHolderAssertion;
use Accounting\Assertion\MemberOfAccountOrganizationAssertion;
use Application\Assertion\MemberOfEntityOrganizationAssertion;
use Application\Entity\User;
use People\Assertion\CommonOrganizationAssertion;
use People\Assertion\MemberOfOrganizationAssertion;
use TaskManagement\Assertion\AcceptedTaskAndMemberSharesNotAssignedAssertion;
use TaskManagement\Assertion\MemberOfOngoingTaskAssertion;
use TaskManagement\Assertion\OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion;
use TaskManagement\Assertion\OwnerOfWorkItemIdeaOrOpenOrCompletedTaskAssertion;
use TaskManagement\Assertion\TaskMemberNotOwnerAndNotCompletedTaskAssertion;
use TaskManagement\Assertion\TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion;
use TaskManagement\Assertion\TaskOwnerAndNotCompletedTaskAssertion;
use TaskManagement\Assertion\TaskOwnerAndOngoingOrAcceptedTaskAssertion;
use TaskManagement\Assertion\TaskOwnerAndOngoingTaskAssertion;
use Zend\Permissions\Acl\Acl;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AclFactory implements FactoryInterface
{
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$acl = new Acl();
		$acl->addRole(User::ROLE_GUEST);
		$acl->addRole(User::ROLE_USER);
		$acl->addRole(User::ROLE_ADMIN, User::ROLE_USER);
		$acl->addRole(User::ROLE_SYSTEM);

		$acl->addResource('Ora\Organization');
		$acl->allow(User::ROLE_USER, 'Ora\Organization', ['People.Organization.userList', 'TaskManagement.Task.list', 'TaskManagement.Stream.list', 'Accounting.Accounts.list'], new MemberOfOrganizationAssertion());
		
		$acl->addResource('Ora\User');
		$acl->allow(User::ROLE_USER, 'Ora\User',['People.Member.get', 'People.User.taskMetrics'], new CommonOrganizationAssertion());
		
		$acl->addResource('Ora\PersonalAccount');
		$acl->addResource('Ora\OrganizationAccount');
		$acl->allow(User::ROLE_USER, 'Ora\PersonalAccount', 'Accounting.Account.get', new MemberOfEntityOrganizationAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\PersonalAccount','Accounting.Account.statement', new AccountHolderAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\OrganizationAccount','Accounting.Account.statement', new MemberOfAccountOrganizationAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\OrganizationAccount','Accounting.Account.deposit', new AccountHolderAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\OrganizationAccount','Accounting.Account.withdrawal', new AccountHolderAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\OrganizationAccount', 'Accounting.Account.incoming-transfer', new AccountHolderAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\OrganizationAccount', 'Accounting.Account.outgoing-transfer', new AccountHolderAssertion());

		$acl->addResource('Ora\Task');
		$acl->allow(User::ROLE_USER, null, 'TaskManagement.Task.create');
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.get', new MemberOfEntityOrganizationAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.join', new OrganizationMemberNotTaskMemberAndNotCompletedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.estimate', new MemberOfOngoingTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.unjoin', new TaskMemberNotOwnerAndNotCompletedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', ['TaskManagement.Task.edit', 'TaskManagement.Task.delete'], new TaskOwnerAndNotCompletedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.execute', new OwnerOfWorkItemIdeaOrOpenOrCompletedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.complete', new TaskOwnerAndOngoingOrAcceptedTaskAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.accept', new TaskOwnerAndCompletedTaskWithEstimationProcessCompletedAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Task.assignShares', new AcceptedTaskAndMemberSharesNotAssignedAssertion());
		$acl->allow(User::ROLE_USER, 'Ora\Task', 'TaskManagement.Reminder.add-estimation', new TaskOwnerAndOngoingTaskAssertion());
		
		$acl->allow(User::ROLE_SYSTEM, null, array('TaskManagement.Task.closeTasksCollection', 'TaskManagement.Reminder.assignment-of-shares'));
		
		return $acl;
	}
}
