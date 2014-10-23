Feature: Testing the RESTfulness of the Task Controller (JOIN USER INTO MEMBERS/TEAM OF EXISTING TASK)

@task @members @jointeam @POST
Scenario: Join user into members of existing task
	Given that I want to make a new "Member"
	When I request "/task-management/tasks/1/members/1"
	Then the response status code should be 201

@task @members @jointeam @POST
Scenario: Join user into members of not existing task
	Given that I want to make a new "Member"
	When I request "/task-management/tasks/IDNONVALIDO/members/1"
	Then the response status code should be 404
	
@task @members @jointeam @POST
Scenario: Join not existing user into members of existing task
	Given that I want to make a new "Member"
	When I request "/task-management/tasks/1/members/IDNONVALIDO"
	Then the response status code should be 404
	
@task @members @jointeam @POST
Scenario: Join user into members of existing task withoud specify user id
	Given that I want to make a new "Member"
	When I request "/task-management/tasks/1/members/"
	Then the response status code should be 406