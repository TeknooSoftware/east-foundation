Feature: Streamed response
  As a developer, I need to send the body of a response chunk by chunk, or as server-sent events, with the Symfony
  bridge, without the buffer of the full body, and to stop the generator of the chunks when the HTTP client closes the
  connection.

  Scenario: Send the chunks of a generator one by one
    Given I have DI initialized
    And I register a router
    And The router can process the request "/stream" to a generator of the chunks "foo,bar,baz"
    When The server will receive the request "https://foo.com/stream"
    Then The client must accept a psr response
    And The Symfony bridge must send a streamed response with the status 200 and the content type "text/plain"
    And The Symfony bridge must flush the chunks "foo,bar,baz" one by one

  Scenario: Send the server-sent events of a generator one by one
    Given I have DI initialized
    And I register a router
    And The router can process the request "/stream" to a generator of server-sent events
    When The server will receive the request "https://foo.com/stream"
    Then The client must accept a psr response
    And The Symfony bridge must send a streamed response with the status 200 and the content type "text/event-stream"
    And The Symfony bridge must flush the events:
      """
      event: message_start
      data: {"type":"message_start"}

      : keep-alive

      data: foo
      data: bar
      """

  Scenario: Stop the generator when the HTTP client closes the connection
    Given I have DI initialized
    And I register a router
    And The router can process the request "/stream" to a generator of the chunks "foo,bar,baz"
    And The HTTP client closes the connection after the chunk 2
    When The server will receive the request "https://foo.com/stream"
    Then The client must accept a psr response
    And The Symfony bridge must flush the chunks "foo,bar" one by one
    And The generator must be stopped after the chunks "foo,bar"

  Scenario: Send a body without generator in one response
    Given I have DI initialized
    And I register a router
    And The router can process the request "/foo/bar" to recipe "standard" to return a "psr" response
    When The server will receive the request "https://foo.com/foo/bar?test=barFoo"
    Then The client must accept a psr response
    And The Symfony bridge must send a response with the body "barFoohttps://foo.com/foo/bar?test=barFoo"
