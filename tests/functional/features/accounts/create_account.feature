Feature: Create personal account
  As a user
  I want to have a personal account when I join an organization
  in order to collect and use my credits

  Scenario: Successfully getting organization account statement
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Account"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/organization-statement"
    Then the response status code should be 200
    And the response should be JSON
    And the response should have a "organization" property
    And the response should have a "transactions" property

  Scenario: Successfully deposit 500 credits into the organization account
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Deposit"
    And that its "amount" is "500"
    And that its "description" is "My first deposit"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/accounts/dcde992b-5aa9-4447-98ae-c8115906dcb7/deposits"
    Then the response status code should be 201

  Scenario: Successfully deposit 800.50 credits into the organization account without a description
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Deposit"
    And that its "amount" is "800.50"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/accounts/dcde992b-5aa9-4447-98ae-c8115906dcb7/deposits"
    Then the response status code should be 201

  Scenario: Cannot deposit 0 credits into the organization account
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Deposit"
    And that its "amount" is "0"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/accounts/dcde992b-5aa9-4447-98ae-c8115906dcb7/deposits"
    Then the response status code should be 400

  Scenario: Cannot deposit -100 credits into the organization account
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Deposit"
    And that its "amount" is "-100"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/accounts/dcde992b-5aa9-4447-98ae-c8115906dcb7/deposits"
    Then the response status code should be 400

  Scenario: Cannot deposit wrong number of credits into the organization account
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Deposit"
    And that its "amount" is "#wrong_"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/accounts/dcde992b-5aa9-4447-98ae-c8115906dcb7/deposits"
    Then the response status code should be 400

  Scenario: Cannot deposit without a number of credits into the organization account
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to make a new "Deposit"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/accounts/dcde992b-5aa9-4447-98ae-c8115906dcb7/deposits"
    Then the response status code should be 400
