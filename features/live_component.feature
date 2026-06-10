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
