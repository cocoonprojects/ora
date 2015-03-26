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

Scenario: Requesting the list of tasks of a stream
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	And that its "streamID" is "00000000-1000-0000-0000-000000000000"
	When I request "/task-management/tasks"
	Then the response status code should be 200
	And the response should be JSON
	And the response should have a "tasks" property

Scenario: Requesting a task that the first member evaluated 1500 credits and the second skipped
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000107"
	Then the response status code should be 200
	And the response should be JSON
	And the "estimation" property should be "1500"
	And the "members" property size should be "2"

Scenario: Requesting a task with skipped estimation by the only member 
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000106"
	Then the response status code should be 200
	And the response should be JSON
	And the "estimation" property should be "-1"
	And the "members" property size should be "1"

Scenario: Requesting a task estimated by only one member 
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000108"
	Then the response status code should be 200
	And the response should be JSON
	And the response shouldn't have a "estimation" property
	And the "members" property size should be "2"

Scenario: Checking functionality on an ongoing tasks of a stream
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000000"
	Then the response status code should be 200
	And the response should be JSON
	And the response should have a "_links" property
	Then the "_links" property contains "complete" key
	Then the "_links" property contains "delete" key
	Then the "_links" property contains "estimate" key
	Then the "_links" property contains "self" key
	Then the "_links" property contains "edit" key

Scenario: Checking functionality on a completed tasks of a stream
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000001"
	Then the response status code should be 200
	And the response should be JSON
	And the response should have a "_links" property
	Then the "_links" property contains "estimate" key
	Then the "_links" property contains "self" key
	Then the "_links" property contains "execute" key

Scenario: Checking functionality on an accepted tasks of a stream
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000002"
	Then the response status code should be 200
	And the response should be JSON
	And the response should have a "_links" property
	Then the "_links" property contains "assignShares" key
	Then the "_links" property contains "self" key
	Then the "_links" property contains "complete" key
	
