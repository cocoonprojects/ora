Feature: Create task
	As an organization member
	I want to create a new task into one of my organization streams
	in order to allow the team to start the estimation

Scenario: Successfully creating a task with fixtures
	Given there are the following organizations:
	| name     | email        |
	| ideato   | mr@ideato.it |
	And that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Task"
	And that its "subject" is "My First Task"
	And that its "streamID" is "00000000-1000-0000-0000-000000000000"
	When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
	Then the response status code should be 201
	And the header "Location" should be "/task-management/tasks/[0-9a-z\-]+"
