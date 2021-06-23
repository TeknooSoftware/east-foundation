Feature: HTTP
  As a developer, I need a library able to create a platform in PHP to receive and answer to HTTP request,
  following the #East programming philosophy, compatible with PHP PSRs and Composer and interoperable with
  a large panel of framework, like Symfony or Zend.

  Scenario: Ignore a request not mapped by the router
    Given I have DI initialized
    And I register a router
    And The router can process the request "/foo/bar" to controller "closureFoo"
    When The server will receive the request "https://foo.com/bar/"
    Then The client must not accept a response.

  Scenario: Execute a request mapped by the router
    Given I have DI initialized
    And I register a router
    And The router can process the request "/foo/bar" to controller "closureFoo"
    When The server will receive the request "https://foo.com/foo/bar?test=fooBar"
    Then The client must accept a psr response
    And I should get as response "fooBar"

  Scenario: Execute a request mapped by the router to a recipe
    Given I have DI initialized
    And I register a router
    And The router can process the request "/foo/bar" to recipe "barFoo" to return a "psr" response
    When The server will receive the request "https://foo.com/foo/bar?test=barFoo"
    Then The client must accept a psr response
    And I should get as response "barFoohttps://foo.com/foo/bar?test=barFoo"

  Scenario: Execute a request mapped by the router to a recipe
    Given I have DI initialized
    And I register a router
    And The router can process the request "/foo/bar" to recipe "barFoo" to return a "east" response
    When The server will receive the request "https://foo.com/foo/bar?test=barFoo"
    Then The client must accept a east response
    And I should get as response "barFoo"

  Scenario: Execute a request mapped by the router to a recipe
    Given I have DI initialized
    And I register a router
    And The router can process the request "/foo/bar" to recipe "barFoo" to return a "json" response
    When The server will receive the request "https://foo.com/foo/bar?test=barFoo"
    Then The client must accept a json response
    And I should get as response '{"foo": "barFoo"}'

  Scenario: Execute a request mapped by the router to a recipe, but ignore the missing response
    Given I have DI initialized
    And client are configured to ignore missing response
    And I register a router
    And The router can process the request "/foo/bar" to recipe "fooBar" to return a "psr" response
    When The server will receive the request "https://foo.com/foo/bar?test=barFoo"
    And I should get nothing

  Scenario: Execute a request mapped by the router to a recipe, but not ignore the missing response
    Given I have DI initialized
    And I register a router
    And The router can process the request "/foo/bar" to recipe "fooBar" to return a "psr" response
    When The server will receive the request "https://foo.com/foo/bar?test=barFoo"
    Then The client must throw an exception

  Scenario: Execute a request mapped by the router but an error will be occurring
    Given I have DI initialized
    And I register a router
    And The router can process the request "/foo/bar" to controller "closureFoo"
    When The server will receive the request "https://foo.com/foo/bar?bar=fooBar"
    Then The client must accept an error
