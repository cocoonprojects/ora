Feature: List tasks
	As an organization member
	I want to read the list of tasks available
	in order to understand their current status, members count and how I can contribute

Scenario: Requesting the list of available task without any parameters
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks"
	Then the response status code should be 200
	And the response is JSON
	And the response has a "tasks" property

Scenario: Requesting the list of available task checking they have an avg estimation
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks"
	Then the response status code should be 200
	And the response is JSON
	And the array "tasks" in JSON response has elements with "members" property
	
Scenario: Requesting the list of available task then the specified task must have xxx avg value 
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks"
	Then the response is JSON
	And the value of property "estimation" of the element with property "id" with value "00000000-0000-0000-0000-000000000108" in array "tasks" should be "1500"
	
Scenario: Requesting the list of available task then the specified task must have - avg value 
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks"
	Then the response is JSON
	And the value of property "estimation" of the element with property "id" with value "00000000-0000-0000-0000-000000000104" in array "tasks" should be "-"
	
Scenario: Requesting the list of available task then the specified task must have - avg value 
    Given that I am authenticated as "mark.rogers@ora.local" 
    And that I want to find a "Task"
	When I request "/task-management/tasks"
	Then the response is JSON
	And the value of property "estimation" of the element with property "id" with value "00000000-0000-0000-0000-000000000107" in array "tasks" should be "N.A."
