Feature: Testing the RESTfulness of the Task Controller (CREATION OF A NEW TASK)

@task @create @POST
Scenario: Creating a new Task with right parameters
	Given that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "projectID" is "1"
	When I request "/task-management/task"
	Then the response status code should be 201
	
@task @create @POST
Scenario: Creating a new Task without parameters
	Given that I want to make a new "Task"
	When I request "/task-management/task"
	Then the response status code should be 400

@task @create @POST
Scenario: Creating a new Task with empty projectID
	Given that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "projectID" is ""
	When I request "/task-management/task"
	Then the response status code should be 406

@task @create @POST
Scenario: Creating a new Task with not existing projectID
	Given that I want to make a new "Task"
	And that its "subject" is "UNA ROTONDA SUL MARE"
	And that its "projectID" is "THIS_IS_A_NOT_EXISTING_PROJECT_ID"
	When I request "/task-management/task"
	Then the response status code should be 404
	
@task @create @POST
Scenario: Creating a new Task with empty subject
	Given that I want to make a new "Task"
	And that its "subject" is ""
	And that its "projectID" is "1"
	When I request "/task-management/task"
	Then the response status code should be 406