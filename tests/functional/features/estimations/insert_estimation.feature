Feature: Estimate a task
	As a task member
	I want to be able to estimate on ongoing task
	in order to contribute to task estimation

Scenario: Cannot estimate a task without an estimation value
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	When I request "/task-management/tasks/156eaec0-f997-4efe-94c9-b8c15da1f779/estimations"
	Then the response status code should be 400

Scenario: Cannot estimate a task with negative estimation value
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "-100"
	When I request "/task-management/tasks/156eaec0-f997-4efe-94c9-b8c15da1f779/estimations"
	Then the response status code should be 400

Scenario: Cannot estimate a task with invalid estimation value
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "estimation"
	When I request "/task-management/tasks/156eaec0-f997-4efe-94c9-b8c15da1f779/estimations"
	Then the response status code should be 400

Scenario: Successfully estimating a task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "100"
	When I request "/task-management/tasks/156eaec0-f997-4efe-94c9-b8c15da1f779/estimations"
	Then the response status code should be 201

Scenario: Successfully skipping the estimation of a task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "-1"
	When I request "/task-management/tasks/2662e530-b58e-4dfc-9d0a-e140c2a62610/estimations"
	Then the response status code should be 201

Scenario: Successfully assigning no value to a task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "0"
	When I request "/task-management/tasks/8924d278-4bb5-4f16-90d6-ee08aa639d88/estimations"
	Then the response status code should be 201

Scenario: Cannot estimate a not existing task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "200"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000888/estimations"
	Then the response status code should be 404

Scenario: Cannot estimate a completed task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "150"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000001/estimations"
	Then the response status code should be 406	

Scenario: Cannot estimate an accepted task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "150"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000002/estimations"
	Then the response status code should be 406	

Scenario: Cannot estimate a deleted task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "150"
	When I request "/task-management/tasks/00000000-0000-0000-0000-000000000003/estimations"
	Then the response status code should be 406	

Scenario: Cannot estimate if the member has already estimate the task
	Given that I am authenticated as "mark.rogers@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "250"
	When I request "/task-management/tasks/156eaec0-f997-4efe-94c9-b8c15da1f779/estimations"
	Then the response status code should be 204
	
Scenario: Cannot estimate if not member of the task
	Given that I am authenticated as "phil.toledo@ora.local"
	And that I want to make a new "Estimation"
	And that its "value" is "300"
	When I request "/task-management/tasks/2662e530-b58e-4dfc-9d0a-e140c2a62610/estimations"
	Then the response status code should be 401