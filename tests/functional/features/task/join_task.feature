Feature: Join a task
	As an organization member
	I want to join an ongoing task
	in order to be part of the team that will accomplish the task and estimate it

Scenario: Successfully joining an ongoing task as logged user
	Given that I am authenticated as "phil.toledo@ora.local" 
	And that I want to make a new "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000000/members"
	Then the response status code should be 201

Scenario: Joining an ongoing task the logged user is already member of is invariant
	Given that I am authenticated as "phil.toledo@ora.local" 
	And that I want to make a new "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000000/members"
	Then the response status code should be 204
	
Scenario: Cannot join a non existing task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-0000000000x0/members"
	Then the response status code should be 404
	
Scenario: Cannot join a completed task
	Given that I am authenticated as "phil.toledo@ora.local" 
	And that I want to make a new "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000001/members"
	Then the response status code should be 412

Scenario: Cannot join an accepted task
	Given that I am authenticated as "phil.toledo@ora.local" 
	And that I want to make a new "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000002/members"
	Then the response status code should be 412