Feature: Get organization statement
  As an organization member
  I want to get the organization member
  In order to understand how credits are used
@wip
  Scenario: Successfully getting an organization statement
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Statement"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/organization-statement"
    Then the response status code should be 200
    And the response should be JSON
    And the "transactions[0].type" property should be "IncomingTransfer"
    And the "transactions[1].type" property should be "OutgoingTransfer"
    And the "transactions[2].type" property should be "Withdrawal"
    And the "transactions[3].type" property should be "Deposit"