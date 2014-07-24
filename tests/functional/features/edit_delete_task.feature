Feature: edit or delete a task

	In order to fix mistakes made during creation of a task
	As a task owner
	I want to edit (and delete) an ongoing task


Scenario: can edit a task
	Given I am authenticated on ORA Project
	When I get the list of ongoing tasks
	And the list of ongoing tasks is not empty
	When I am task owner of a task
	Then I should see edit button		

Scenario: can't edit a task
	Given I am authenticated on ORA Project
        When I get the list of ongoing tasks
        And the list of ongoing tasks is not empty
        When I am not task owner of a task                 
        Then I should not see edit button

Scenario: can delete a task
	Given I am authenticated on ORA Project
        When I get the list of ongoing tasks
        And the list of ongoing tasks is not empty
        When I am task owner of a task                 
        Then I should see delete button

Scenario: can't delete a task
	Given I am authenticated on ORA Project
        When I get the list of ongoing tasks
        And the list of ongoing tasks is not empty
        When I am not task owner of a task                 
        Then I should not see delete  button

