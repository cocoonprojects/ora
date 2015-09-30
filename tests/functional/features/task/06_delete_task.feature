Feature: Delete Task
	As a task owner
	I want to delete an ongoing task
	in order to remove it definitely from the system

Scenario: Successfully deleting an ongoing task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000000"
	Then the response status code should be 200
	
Scenario: Cannot delete a not existing task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-0000000000x0"
	Then the response status code should be 404

# TODO: duplicato, da rimuovere
Scenario: Cannot delete a not existing task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/1"
	Then the response status code should be 404

Scenario: Cannot delete the entire tasks collection
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
	Then the response status code should be 405
	
Scenario: Cannot delete a completed task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000001"
	Then the response status code should be 412
	

Scenario: Cannot delete an accepted task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000002"
	Then the response status code should be 412
	
Scenario: Deleting a deleted task is invariant
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000003"
	Then the response status code should be 204
	
