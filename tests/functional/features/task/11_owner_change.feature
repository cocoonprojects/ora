Feature: Change task owner
  As an organization admin
  I want to make a call
  in order to change the task owner

  Scenario: Successfully change task owner
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Member"
    And that its "ownerId" is "80000000-0000-0000-0000-000000000000"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000107/owner"
    Then the response status code should be 201
    And echo last response

#  Scenario: Cannot change task owner if not organization admin
#    Given that I am authenticated as "paul.smith@ora.local"
#    And that I want to make a new "Member"
#    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000107/owner"
#    Then the response status code should be 204