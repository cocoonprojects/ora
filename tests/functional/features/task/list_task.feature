Feature: List tasks
	As an organization member
	I want to read the list of tasks available
	in order to understand their current status, members count and how I can contribute

Scenario: Requesting the list of available task without any parameters
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks"
	Then the response is JSON
	And the response has a "tasks" property
	Then the response status code should be 200
