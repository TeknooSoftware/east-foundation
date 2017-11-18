Feature: HTTP
  As a developer, I need a library able to create a platform in PHP to receive and answer to HTTP request,
  following the #East programming philosophy, compatible with PHP PSRs and Composer and interoperable with
  a large panel of framework, like Symfony or Zend.

  Scenario: Ignore a request not mapped by the router
    Given I have an empty recipe
    And I register the processor "Teknoo\East\Foundation\Processor\Processor"
    And I register a router
    And The router can process the request "/foo/bar" to controller "closureFoo"
    When The client will receive the request "https://foo.com/bar/"
    Then The client must not accept a response.

  Scenario: Execute a request mapped by the router
    Given I have an empty recipe
    And I register the processor "Teknoo\East\Foundation\Processor\Processor"
    And I register a router
    And The router can process the request "/foo/bar" to controller "closureFoo"
    When The client will receive the request "https://foo.com/foo/bar?test=fooBar"
    Then The client must accept a response
    And I should get "fooBar"

  Scenario: Execute a request mapped by the router but an error will be occurring
    Given I have an empty recipe
    And I register the processor "Teknoo\East\Foundation\Processor\Processor"
    And I register a router
    And The router can process the request "/foo/bar" to controller "closureFoo"
    When The client will receive the request "https://foo.com/foo/bar?bar=fooBar"
    Then The client must accept an error
