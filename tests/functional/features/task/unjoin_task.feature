Feature: Unjoin a task
	As a member of a task if I haven't estimated it yet
	I want to unjoin the task
	in order to not be involved any more in task related activities

@task @members @unjointeam @POST
Scenario: Successfully unjoining an existing task the logged user is member of
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000003/members"
	Then the response status code should be 200

@task @members @unjointeam @POST
Scenario: Unjoining an existing task which the logged user isn't member of is invariant
	Given that I am authenticated as "phil.toledo@ora.local" 
	And that I want to delete a "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000004/members"
	Then the response status code should be 204
	
@task @members @unjointeam @POST
Scenario: Cannot unjoin a not existing task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-0000000000x0/members"
	Then the response status code should be 404
	
@task @members @unjointeam @POST
Scenario: Cannot unjoin a completed task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000001/members"
	Then the response status code should be 406

@task @members @unjointeam @POST
Scenario: Cannot unjoin an accepted task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to delete a "Member"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000002/members"
	Then the response status code should be 406