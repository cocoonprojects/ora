Feature: Edit task
	As a task owner
	I want to edit an ongoing task
	in order to fix mistakes made during creation

@task @edit @PUT
Scenario: Successfully updating an existing Task with a new subject
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to update a "Task"
	And that its "subject" is "This update subject is a lot better than the previous one"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000003"
	Then the response status code should be 202
	
@task @edit @PUT
Scenario: Cannot update an existing Task with an empty subject
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to update a "Task"
	And that its "subject" is ""
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000003"
	Then the response status code should be 406
	
@task @edit @PUT
Scenario: Cannot update an existing Tasks with nothing to change
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to update a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000003"
	Then the response status code should be 204
	
@task @edit @PUT
Scenario: Cannot update the entire collection of existing Tasks
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to update a "Task"
	And that its "subject" is "This update subject is a lot better than the previous one"
	When I request "/task-management/tasks"
	Then the response status code should be 405
	
Scenario: Successfully updating a completed task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to update a "Task"
	And that its "subject" is "This update subject is a lot better than the previous one"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000001"
	Then the response status code should be 202

Scenario: Successfully updating an accepted task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to update a "Task"
	And that its "subject" is "This update subject is a lot better than the previous one"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000002"
	Then the response status code should be 202
	