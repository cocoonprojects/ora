Feature: View User Profile page
  As an organization member
  I want to view user profile page
  in order to analyze user profile informations

  Scenario: Successfully getting a member profile
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "UserProfilePage"
    When I request "/00000000-0000-0000-1000-000000000000/people/members/80000000-0000-0000-0000-000000000000"
    Then the response status code should be 200
    And the response should be JSON
    And the "id" property should be "80000000-0000-0000-0000-000000000000"
    And the "firstname" property should be "Bruce"
    And the "lastname" property should be "Wayne"
    And the "email" property should be "bruce.wayne@ora.local"
    And the "role" property should be "member"

  Scenario: Cannot get the profile if requesting as a not existing user
    Given that I am authenticated as "pippo@ora.local"
    And that I want to find a "UserProfilePage"
    When I request "/00000000-0000-0000-1000-000000000000/people/members/80000000-0000-0000-0000-000000000000"
    Then the response status code should be 401

  Scenario: Cannot get a user profile if not an organization member
    Given that I am authenticated as "paul.smith@ora.local"
    And that I want to find a "UserProfilePage"
    When I request "/00000000-0000-0000-1000-000000000000/people/members/80000000-0000-0000-0000-000000000000"
    Then the response status code should be 403

  Scenario: Cannot get a user profile of a not existing member
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "UserProfilePage"
    When I request "/00000000-0000-0000-1000-000000000000/people/members/80000000-0000-0000-0000-0000000000xx"
    Then the response status code should be 404

  Scenario: Cannot getting a user profile into a not existing organization
    Given that I am authenticated as "mark.rogers@ora.local"
    And that I want to find a "UserProfilePage"
    When I request "/00000000-0000-0000-1000-0000000000xx/people/members/80000000-0000-0000-0000-000000000000"
    Then the response status code should be 404

