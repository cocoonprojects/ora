Feature: Testing the RESTfulness of the Task Controller

Scenario: Creating a new Task with right parameters
	Given that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "projectID" is "1"
	When I request "/task-management/task"
	Then the response status code should be 201
	
Scenario: Creating a new Task without parameters
	Given that I want to make a new "Task"
	When I request "/task-management/task"
	Then the response status code should be 400