Feature: Testing the RESTfulness of the Task Controller (UNJOIN USER FROM MEMBERS/TEAM OF TASK)

@task @members @unjointeam @POST
Scenario: Unjoin user into members of existing task
	Given that I want to delete a "Member"
	When I request "/task-management/tasks/1/members/2"
	Then the response status code should be 200

@task @members @unjointeam @POST
Scenario: Unjoin user into members of existing task when user is not member of team
	Given that I want to delete a "Member"
	When I request "/task-management/tasks/3/members/1"
	Then the response status code should be 403
	
@task @members @unjointeam @POST
Scenario: Unjoin user into members of not existing task
	Given that I want to delete a "Member"
	When I request "/task-management/tasks/IDNONVALIDO/members/1"
	Then the response status code should be 404
	
@task @members @unjointeam @POST
Scenario: Unjoin not existing user into members of existing task
	Given that I want to delete a "Member"
	When I request "/task-management/tasks/1/members/IDNONVALIDO"
	Then the response status code should be 404
	
@task @members @unjointeam @POST
Scenario: Unjoin user into members of existing task without specify user id
	Given that I want to delete a "Member"
	When I request "/task-management/tasks/1/members/"
	Then the response status code should be 404