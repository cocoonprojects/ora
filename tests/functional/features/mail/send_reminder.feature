Feature: Send a Reminder to Speed Up Task Estimation
  As an organization member
  I want to send a reminder
  in order to speed up a task estimation in task with OnGoing status

  Scenario: Successfully sending an 'add-estimation' reminder as task owner
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Reminder"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000004/reminders/add-estimation"
    Then the response status code should be 201

  Scenario: Cannot send an 'add-estimation' reminder as task member
    Given that I am authenticated as "phil.toledo@ora.local"
    And that I want to make a new "Reminder"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000004/reminders/add-estimation"
    Then the response status code should be 403

  Scenario: Cannot send a reminder that does not exists
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Reminder"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000004/reminders/wrong-type"
    Then the response status code should be 404

  Scenario: Cannot send a reminder without specifying the type
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Reminder"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/00000000-0000-0000-0000-000000000004/reminders/"
    Then the response status code should be 404

  Scenario: Cannot send an 'add-estimation' reminder for a non existing task
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Reminder"
    When I request "/00000000-0000-0000-1000-000000000000/task-management/tasks/10000000-0000-0000-0000-000000000000/reminders/add-estimation"
    Then the response status code should be 404
