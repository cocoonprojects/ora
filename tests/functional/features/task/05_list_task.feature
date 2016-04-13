Feature: List tasks
  As an organization member
  I want to read the list of tasks available
  in order to understand their current status, members count and how I can contribute

  Scenario: Successfully getting the list of available tasks without any parameters
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property

  Scenario: Successfully getting the list of tasks of a stream
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "streamID" is "00000000-1000-0000-0000-000000000000"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property

  Scenario: Successfully getting a task that the first member evaluated 1500 credits and the second skipped
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000107"
    Then the response status code should be 200
    And the response should be JSON
    And the "estimation" property should be "1500"
    And the "members" property size should be "2"

  Scenario: Successfully getting a task with skipped estimation by the only member
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000106"
    Then the response status code should be 200
    And the response should be JSON
    And the "estimation" property should be "-1"
    And the "members" property size should be "1"

  Scenario: Successfully getting a task estimated by only one member
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000108"
    Then the response status code should be 200
    And the response shouldn't have a "_embedded.ora:task.estimation" property
    And the "members" property size should be "2"

  Scenario: Successfully getting command list on an ongoing tasks of a stream
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000004"
    Then the response status code should be 200
    And the response should have a "_links" property
    And the response should have a "_links.self" property
    And the response shouldn't have a "_links.ora:delete" property
    And the response should have a "_links.ora:estimate" property
    And the response should have a "_links.ora:edit" property

  Scenario: Successfully getting command list on a completed tasks of a stream
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000001"
    Then the response status code should be 200
    And the response should have a "_links" property
    And the response should have a "_links.self" property
    And the response shouldn't have a "_links.next" property
    And the response should have a "_links.ora:execute" property

  Scenario: Successfully getting command list on an accepted tasks of a stream for the task owner that have assigned shares on that task
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000002"
    Then the response status code should be 200
    And the response should have a "_links" property
    And the response should have a "_links.self" property
    And the response shouldn't have a "_links.ora:assignShares" property
    And the response should have a "_links.ora:complete" property

  Scenario: Successfully getting command list on an accepted tasks of a stream for the task owner that haven't assigned shares on that task
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000101"
    Then the response status code should be 200
    And the response should have a "_links" property
    And the response should have a "_links.self" property
    And the response should have a "_links.ora:assignShares" property
    And the response should have a "_links.ora:complete" property

  Scenario: Successfully getting a paginated list of available tasks without any parameters
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "limit" is "1"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the response should have a "_links.next" property

  Scenario: Successfully getting a list of tasks until a specified ISO 8601 date
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "endOn" is "2014-07-01T00:00:00.000Z"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the response shouldn't have a "_links.next" property
    And the "total" property should be "4"

  Scenario: Successfully getting a list of tasks from a specified ISO 8601 date
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "startOn" is "2014-07-01T00:00:00.000Z"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the response shouldn't have a "_links.next" property
    And the "total" property should be "10"

  Scenario: Successfully getting a list of tasks until a specified period
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "startOn" is "2014-02-01T00:00:00.000Z"
    And that its "endOn" is "2014-07-01T00:00:00.000Z"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the response shouldn't have a "_links.next" property
    And the "total" property should be "2"

  Scenario: Successfully getting an empty list of tasks filtered by user email
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "memberEmail" is "example@ora.org"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the response shouldn't have a "_links.next" property
    And the "count" property should be "0"

  Scenario: Successfully getting a list of tasks filtered by user id
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "memberId" is "80000000-0000-0000-0000-000000000000"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the response shouldn't have a "_links.next" property
    And the "count" property should be "2"
  
  Scenario: Successfully getting an empty list of tasks filtered by status
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "status" is "10"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the "count" property should be "0"
    
  Scenario: Successfully getting an empty list of tasks filtered by wrong status
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "status" is "Pippo"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the response shouldn't have a "_links.next" property
    And the "count" property should be "0"

  Scenario: Successfully getting a list of tasks filtered by status
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "status" is "20"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the "count" property should be "3"

  Scenario: Successfully getting the all list of tasks using an unspecified status
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    And that its "status" is ""
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks"
    Then the response status code should be 200
    And the response should have a "_embedded.ora:task" property
    And the "count" property should be "10"
    
  Scenario: Cannot get command list on accepted kanbanize tasks for a task owner
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000106"
    Then the response status code should be 200
    And the response should have a "_links" property
    And the response should have a "_links.self" property
    And the response should have a "_links.ora:assignShares" property
    And the response shouldn't have a "_links.ora:complete" property
    
  Scenario: Cannot get command list on a kanbanize task for a task owner
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000108"
    Then the response status code should be 200
    And the response should have a "_links" property
    And the response should have a "_links.self" property
    And the response shouldn't have a "_links.ora:execute" property
    And the response shouldn't have a "_links.ora:accept" property
    And the "status" property should be "30"

    
  Scenario: Successfully getting command list on a accepted task with shares assignment process completed for a task owner
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Task"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000901"
    Then the response status code should be 200
    And the response should have a "_links" property
    And the response should have a "_links.self" property
    And the response shouldn't have a "_links.ora:execute" property
    And the response should have a "_links.ora:complete" property
    And the response should have a "_links.ora:close" property
    And the "status" property should be "40"
