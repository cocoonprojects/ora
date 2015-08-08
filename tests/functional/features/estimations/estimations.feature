Feature: Estimation list
	As an organization member
	I want to see who already assigned an estimation
	in order to understand how the estimation progress

Scenario: Seeing who already estimated a task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to find a "members who estimated a task"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
	Then the response status code should be 200
	And the response should have a "/estimation\.value$/" property