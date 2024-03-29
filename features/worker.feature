Feature: Worker
  As a developer, I need a library able to create a platform in PHP with some background worker in CLI mode, following
  the #East programming philosophy, compatible with PHP PSRs and Composer and interoperable with a large panel of
  framework, like Symfony or Zend.

  Scenario: Wait a few moments without blocking
    Given I have DI initialized
    And a cli agent
    And a timer action to ping a message to a log each "2" seconds
    When the agent sleeps "10" seconds
    Then the main function has been paused for "10" seconds
    And the logs have "5" lines

  Scenario: Task in time limit
    Given I have DI initialized
    And a cli agent
    And a liveness behavior build on event on a file "ping"
    And each task must be limited in time of "5" seconds and killed when they exceed it.
    When the agent start a short task
    Then task must be finished
    And no exception must be throwed

  Scenario: Task too long
    Given I have DI initialized
    And a cli agent
    And a liveness behavior build on event on a file "ping"
    And each task must be limited in time of "5" seconds and killed when they exceed it.
    When the agent start a too long task
    Then An exception must be catched
    And the task must be not finished
