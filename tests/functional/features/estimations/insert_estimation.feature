Feature: Create new Estimation
	As a task member
	I want to be able to insert my estimation on ongoing task
	in order to contribute to task estimation

Scenario: Successfully creating a new Estimation
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "100"
	When I request "/task-management/tasks/e720064f-670e-41c0-900e-a6f1315622c0/estimation"
	Then the response status code should be 201
	
Scenario: Cannot create a new Estimation with no params
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	When I request "/task-management/tasks/29a83bd7-a40c-4300-b06e-fb20ca05d754/estimation"
	Then the response status code should be 400

Scenario: Cannot create a new Estimation with wrong params
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "estimation"
	When I request "/task-management/tasks/29a83bd7-a40c-4300-b06e-fb20ca05d754/estimation"
	Then the response status code should be 400

Scenario: Cannot create a new Estimation with not existing task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "200"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000888/estimation"
	Then the response status code should be 404

#Test unavailable, missing TaskAccepted event
#Scenario: Cannot create a new Estimation if the task is not in ongoing status
#	Given that I am authenticated as "mark.rogers@ora.local"
#	And that I want to make a new "Estimation"
#	And that its "value" is "150"
#	When I request "/task-management/tasks/3f9c3c4a-7e48-4765-9400-95f64baba7da/estimation"
#	Then the response status code should be 406	

Scenario: Cannot create a new Estimation if the member has already estimate the task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "250"
	When I request "/task-management/tasks/e720064f-670e-41c0-900e-a6f1315622c0/estimation"
	Then the response status code should be 204
	
Scenario: Cannot create a new Estimation if the user is not member of the task
	Given that I am authenticated as "phil.toledo@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "300"
	When I request "/task-management/tasks/29a83bd7-a40c-4300-b06e-fb20ca05d754/estimation"
	Then the response status code should be 401



