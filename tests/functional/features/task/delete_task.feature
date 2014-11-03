Feature: Delete Task
	As a task owner
	I want to delete an ongoing task
	in order to remove it definitely from the system

@task @DELETE
Scenario: Successfully deleting existing Task with its ID
	Given that I want to delete a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000000"
	Then the response status code should be 200
	
@task @DELETE
Scenario: Deleting with a non existing ID return a non existing Task error
	Given that I want to delete a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-0000000000x0"
	Then the response status code should be 404

@task @DELETE
Scenario: Deleting with a non Uuid return a non existing Task error
	Given that I want to delete a "Task"
	When I request "/task-management/tasks/1"
	Then the response status code should be 404

@task @DELETE
Scenario: Cannot delete the entire tasks collection
	Given that I want to delete a "Task"
	When I request "/task-management/tasks"
	Then the response status code should be 405
	
@task @DELETE
Scenario: Cannot delete a completed task
	Given that I want to delete a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000001"
	Then the response status code should be 406

@task @DELETE
Scenario: Cannot delete an accepted task
	Given that I want to delete a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000002"
	Then the response status code should be 406