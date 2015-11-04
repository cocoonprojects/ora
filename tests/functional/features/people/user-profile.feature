Feature: View User Profile page
	As an organization member
	I want to view user profile page
	in order to analyze user profile informations

Scenario: Successfully getting a user profile page
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to find a "UserProfilePage"
	When I request "/00000000-0000-0000-1000-000000000000/user-profiles/80000000-0000-0000-0000-000000000000"
	Then the response status code should be 200
	And the response should be JSON
	And the response should have a "id" property
	And the response should have a "firstname" property
	And the response should have a "lastname" property
	And the response should have a "email" property
   	And the response should have a "_embedded.ora:organization-membership" property
   	And the response should have a "_embedded.ora:organization-membership.organization" property
   	And the response should have a "_embedded.ora:organization-membership.role" property
    And the response should have a "_embedded.credits" property

Scenario: Cannot getting a user profile page as Fake user
	Given that I am authenticated as "pippo@ora.local" 
	And that I want to find a "UserProfilePage"
	When I request "/00000000-0000-0000-1000-000000000000/user-profiles/80000000-0000-0000-0000-000000000000"
	Then the response status code should be 401
	
Scenario: Cannot getting a user profile page as NO-OrganizationMember
	Given that I am authenticated as "paul.smith@ora.local" 
	And that I want to find a "UserProfilePage"
	When I request "/00000000-0000-0000-1000-000000000000/user-profiles/80000000-0000-0000-0000-000000000000"
	Then the response status code should be 403
	
Scenario: Cannot getting a user profile page with wrong UserId
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to find a "UserProfilePage"
	When I request "/00000000-0000-0000-1000-000000000000/user-profiles/80000000-0000-0000-0000-0000000000xx"
	Then the response status code should be 404
	
Scenario: Cannot getting a user profile page with wrong OrgId
	Given that I am authenticated as "mark.rogers@ora.local" 
	And that I want to find a "UserProfilePage"
	When I request "/00000000-0000-0000-1000-0000000000xx/user-profiles/80000000-0000-0000-0000-000000000000"
	Then the response status code should be 404

