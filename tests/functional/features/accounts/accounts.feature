Feature: Get account stats
  As an organization member
  I want to get account statistics of other members account
  In order to understand their contribution to the organization

  @wip
  Scenario: Successfully getting an organization member account statistics
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "Account"
    And that its "endOn" is "2015-11-01"
    When I request "/00000000-0000-0000-1000-000000000000/accounting/accounts/cdde992b-5aa9-4447-98ae-c8115906dcb9"
    Then the response status code should be 200
    And the response should be JSON
    And the "total" property should be "3600"
    And the "last3M" property should be "100"
    And the "last6M" property should be "1100"
    And the "last1Y" property should be "1600"