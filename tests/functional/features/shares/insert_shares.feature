Feature: Insert Shares
As a user who estimated a task, when the task become accepted,
I want to assign contribution shares to each member
in order to participate at the share assignment to the team

Scenario: Cannot assign shares if you are not member of the task
	Given that I am authenticated as "phil.toledo@ora.local" 
	And that I want to make a new "Share assignement"
	And that its "60000000-0000-0000-0000-000000000000" is "40"
	And that its "20000000-0000-0000-0000-000000000000" is "60"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000002/shares"
	Then the response status code should be 403

Scenario: Cannot assign shares if you haven't estimated the task

Scenario: Cannot assign shares to an ongoing task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Share assignement"
	And that its "60000000-0000-0000-0000-000000000000" is "40"
	And that its "20000000-0000-0000-0000-000000000000" is "60"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000000/shares"
	Then the response status code should be 412

Scenario: Cannot assign shares to a completed task
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Share assignement"
	And that its "60000000-0000-0000-0000-000000000000" is "40"
	And that its "20000000-0000-0000-0000-000000000000" is "60"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000001/shares"
	Then the response status code should be 412
	
Scenario: Cannot assign shares to a subset of members
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Share assignement"
	And that its "60000000-0000-0000-0000-000000000000" is "100"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000002/shares"
	Then the response status code should be 400

Scenario: Cannot assign a total of shares less than 100%
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Share assignement"
	And that its "60000000-0000-0000-0000-000000000000" is "40"
	And that its "20000000-0000-0000-0000-000000000000" is "50"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000002/shares"
	Then the response status code should be 400

Scenario: Cannot assign a total of shares more than 100%
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Share assignement"
	And that its "60000000-0000-0000-0000-000000000000" is "40"
	And that its "20000000-0000-0000-0000-000000000000" is "70"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000002/shares"
	Then the response status code should be 400

Scenario: Successfully assigning shares to the team
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Share assignement"
	And that its "60000000-0000-0000-0000-000000000000" is "40"
	And that its "20000000-0000-0000-0000-000000000000" is "60"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000002/shares"
	Then the response status code should be 201
	
Scenario: Successfully skip shares assignement to the team
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to make a new "Share assignement"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000002/shares"
	Then the response status code should be 201