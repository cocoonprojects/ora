Feature: View User Task Metrics
	As an organization member
	I want to examine user metrics
	in order to understand her level of activity

Scenario: Successfully getting a user task metrics
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to find a "UserTaskMetrics"
	When I request "/00000000-0000-0000-1000-000000000000/users/60000000-0000-0000-0000-000000000000/task-stats"
	Then the response status code should be 200
	And the response should be JSON
	And the response should have a "_embedded" property
	And the response should have a "_embedded.ora:task" property
	And the response should have a "_embedded.ora:task.averageDelta" property
	And the response should have a "_embedded.ora:task.creditsCount" property
	And the response should have a "_embedded.ora:task.ownershipsCount" property
	
Scenario: Cannot getting a user task metrics as Fake user
	Given that I am authenticated as "fake@ora.local" 
	And that I want to find a "UserTaskMetrics"
	When I request "/00000000-0000-0000-1000-000000000000/users/60000000-0000-0000-0000-000000000000/task-stats"
	Then the response status code should be 401

Scenario: Cannot getting a fake user task metrics
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to find a "UserTaskMetrics"
	When I request "/00000000-0000-0000-1000-000000000000/users/55555555-0000-0000-0000-000000000000/task-stats"
	Then the response status code should be 404
	
Scenario: Cannot getting a user task metrics as NO-OrganizationMember
	Given that I am authenticated as "paul.smith@ora.local" 
	And that I want to find a "UserTaskMetrics"
	When I request "/00000000-0000-0000-1000-000000000000/users/60000000-0000-0000-0000-000000000000/task-stats"
	Then the response status code should be 403
