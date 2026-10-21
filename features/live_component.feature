Feature: Symfony UX Live Component routing
  As a developer using the East Foundation Symfony router, I need the router to resolve
  a Symfony UX Live Component request back to its original controller only when the
  signed props checksum is valid, supporting both the Symfony UX 3 checksum format and
  the legacy Symfony UX 2 format.

  Scenario: Route a live component request signed with a valid Symfony UX 3 checksum
    Given I have DI initialized
    And I register a Symfony UX live component router with secret "a-real-secret-value"
    When a live component "UserProfile" requests "/user/profile/456" with a valid UX3 checksum
    Then The client must accept a psr response
    And I should get as response "Hello john_doe"

  Scenario: Route a live component request signed with a valid legacy Symfony UX 2 checksum
    Given I have DI initialized
    And I register a Symfony UX live component router with secret "a-real-secret-value"
    When a live component "UserProfile" requests "/user/profile/456" with a valid legacy checksum
    Then The client must accept a psr response
    And I should get as response "Hello john_doe"

  Scenario: Reject a live component request with an invalid checksum
    Given I have DI initialized
    And I register a Symfony UX live component router with secret "a-real-secret-value"
    When a live component "UserProfile" requests "/user/profile/456" with an invalid checksum
    Then The client must not accept a response.

  Scenario: Refuse a live component request to a route reserved by the access rules
    Given I have DI initialized
    And I register a Symfony UX live component router with secret "a-real-secret-value" and the path "^/admin" reserved to "ROLE_ADMIN"
    And I am authenticated with the roles "ROLE_USER"
    When a live component "UserProfile" requests "/admin/user/456" with a valid UX3 checksum
    Then the access to the targeted route must be denied

  Scenario: Route a live component request to a route granted by the access rules
    Given I have DI initialized
    And I register a Symfony UX live component router with secret "a-real-secret-value" and the path "^/admin" reserved to "ROLE_ADMIN"
    And I am authenticated with the roles "ROLE_USER,ROLE_ADMIN"
    When a live component "UserProfile" requests "/admin/user/456" with a valid UX3 checksum
    Then The client must accept a psr response
    And I should get as response "Hello admin john_doe"

  Scenario: Route a live component request to a route not concerned by the access rules
    Given I have DI initialized
    And I register a Symfony UX live component router with secret "a-real-secret-value" and the path "^/admin" reserved to "ROLE_ADMIN"
    And I am authenticated with the roles "ROLE_USER"
    When a live component "UserProfile" requests "/user/profile/456" with a valid UX3 checksum
    Then The client must accept a psr response
    And I should get as response "Hello john_doe"

  Scenario: Keep a prop starting with an @ as a string in the recipe of the targeted route
    Given I have DI initialized
    And I register a Symfony UX live component router with secret "a-real-secret-value"
    When a live component "UserProfile" requests "/user/theme" with the prop "formTheme" set to "@Foo/bar.html.twig"
    Then The client must accept a psr response
    And I should get as response "Theme @Foo/bar.html.twig"

  Scenario: Restore the query string of the original request for the targeted route
    Given I have DI initialized
    And I register a Symfony UX live component router with secret "a-real-secret-value"
    When a live component "UserProfile" requests "/user/list?order=email&direction=ASC" with a valid UX3 checksum
    Then The client must accept a psr response
    And I should get as response "Sorted by email ASC"
