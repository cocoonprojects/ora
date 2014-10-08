Feature: Testing the RESTfulness of the Task Controller

@task @creation @post
Scenario: Creating a new Task with right parameters
	Given that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "projectID" is "1"
	When I request "/task-management/task"
	Then the response status code should be 201
	
@task @creation @post
Scenario: Creating a new Task without parameters
	Given that I want to make a new "Task"
	When I request "/task-management/task"
	Then the response status code should be 400

@task @creation @post
Scenario: Creating a new Task with empty projectID
	Given that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "projectID" is ""
	When I request "/task-management/task"
	Then the response status code should be 406

@task @creation @post
Scenario: Creating a new Task with empty subject
	Given that I want to make a new "Task"
	And that its "subject" is ""
	And that its "projectID" is "1"
	When I request "/task-management/task"
	Then the response status code should be 406

@task @creation @post
Scenario: Creating a new Task with not validated subject (min chars)
	Given that I want to make a new "Task"
	And that its "subject" is "MIN CHARS"
	And that its "projectID" is "1"
	When I request "/task-management/task"
	Then the response status code should be 406