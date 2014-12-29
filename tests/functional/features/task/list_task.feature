Feature: List tasks
	As an organization member
	I want to read the list of tasks available
	in order to understand their current status, members count and how I can contribute

Scenario: Requesting the list of available tasks without any parameters
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks"
	Then the response status code should be 200
	And the response should be JSON
	And the response should have a "tasks" property

Scenario: Requesting a task that the first member evaluated 1500 credits and the second skipped
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000108"
	Then the response status code should be 200
	And the response should be JSON
	And the "estimation" property should be "1500"
	And the "members" property size should be "2"

Scenario: Requesting a task with skipped estimation by the only member 
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000104"
	Then the response status code should be 200
	And the response should be JSON
	And the "estimation" property should be "-1"
	And the "members" property size should be "1"
	
Scenario: Requesting a task estimated by only one member 
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000107"
	Then the response status code should be 200
	And the response should be JSON
	And the response shouldn't have a "estimation" property
	And the "members" property size should be "2"
