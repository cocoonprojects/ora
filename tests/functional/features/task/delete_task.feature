Feature: Testing the RESTfulness of the Task Controller (DELETE EXISTING TASK)

@task @DELETE
Scenario: Delete existing Task with invalid ID
	Given that I want to delete a "Task"
	When I request "/task-management/tasks/IDNONVALIDO"
	Then the response status code should be 404

@task @DELETE
Scenario: Delete existing Tasks without ID
	Given that I want to delete a "Task"
	When I request "/task-management/tasks"
	Then the response status code should be 405
	
@task @DELETE
Scenario: Delete existing Task with valid ID
	Given that I want to delete a "Task"
	When I request "/task-management/tasks/1"
	Then the response status code should be 200