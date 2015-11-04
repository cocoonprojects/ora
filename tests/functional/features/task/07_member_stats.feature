Feature: View User Task Metrics
	As an organization member
	I want to examine user metrics
	in order to understand her level of activity

Scenario: Successfully getting a user task metrics
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to find a "UserTaskMetrics"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/member-stats/60000000-0000-0000-0000-000000000000"
	Then the response status code should be 200
	And the response should be JSON
	And the response should have a "averageDelta" property
	And the response should have a "creditsCount" property
	And the response should have a "ownershipsCount" property
	And the response should have a "membershipsCount" property
	
Scenario: Cannot get a member statistics if not a member
	Given that I am authenticated as "fake@ora.local" 
	And that I want to find a "UserTaskMetrics"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/member-stats/60000000-0000-0000-0000-000000000000"
	Then the response status code should be 401

Scenario: Cannot get a not existing member statistics
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to find a "UserTaskMetrics"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/member-stats/55555555-0000-0000-0000-000000000000"
	Then the response status code should be 404
	
Scenario: Cannot get a member statistics if not part of the same organization
	Given that I am authenticated as "paul.smith@ora.local" 
	And that I want to find a "UserTaskMetrics"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/member-stats/60000000-0000-0000-0000-000000000000"
	Then the response status code should be 403
