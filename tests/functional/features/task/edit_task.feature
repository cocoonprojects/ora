Feature: Testing the RESTfulness of the Task Controller (EDIT OF EXISTING TASK)

@task @edit @PUT
Scenario: Update existing Task with valid ID and right parameters
	Given that I want to update a "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	When I request "/task-management/task/1"
	Then the response status code should be 202
	
@task @edit @PUT
Scenario: Update existing Task with valid ID and empty subject parameter
	Given that I want to update a "Task"
	And that its "subject" is ""
	When I request "/task-management/task/1"
	Then the response status code should be 406
	
@task @edit @PUT
Scenario: Update existing Tasks with ID but without parameters (Nothing to update)
	Given that I want to update a "Task"
	When I request "/task-management/task/1"
	Then the response status code should be 204
	
@task @edit @PUT
Scenario: Update existing Tasks without ID and without parameters
	Given that I want to update a "Task"
	When I request "/task-management/task"
	Then the response status code should be 405
	
@task @edit @PUT
Scenario: Update existing Tasks without ID but with parameters
	Given that I want to update a "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	When I request "/task-management/task"
	Then the response status code should be 405