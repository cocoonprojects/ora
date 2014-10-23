Feature: Testing the RESTfulness of the Task Controller (LIST AVAILABLE TASKS)

@task @getList @GET
Scenario: Requesting the list of available task without any parameters
	Given that I want to find a "Task"
	When I request "/task-management/tasks"
	Then the response is JSON
	And the response has a "tasks" property
	Then the response status code should be 200