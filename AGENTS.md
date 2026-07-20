# Project Interaction & Contribution Protocol: East Foundation

This document outlines the standards and protocols for all contributors (human or automated) interacting with the `east-foundation` codebase.

## 1. Core Philosophy
All code must adhere to the **#east** philosophy:
- **Immutability & Fluency**: All public methods must return either `$this` (for mutable patterns) or a new instance of `$this` (for immutable patterns).
- **Framework Agnosticism**: The library is designed to be decoupled from any specific framework. It relies on standard interfaces (PSR) to ensure portability.

## 2. Technical Standards
To maintain quality and consistency, all contributions must comply with:

### 2.1 Language & Environment
- **PHP Version**: 8.1 or newer.
- **Coding Standards**: PSR-2 / PSR-12.
- **Dependency Management**: Composer.

### 2.2 Interface Compliance
The project is built upon several PSR standards. All new components must respect these where applicable:
- **PSR-7**: HTTP Message interfaces.
- **PSR-11**: Container interfaces.
- **PSR-15**: HTTP Middleware interfaces.
- **PSR-20**: HTTP Message Date/Time interfaces.

### 2.3 Static Analysis & Type Safety
- **Strict Typing**: All functions and methods should use scalar type hints and return type hints.
- **Static Analysis**: The codebase is verified using **PHPStan**. No new code should introduce static analysis errors.

## 3. Quality Assurance

### 3.1 Testing Requirements
- **Unit Testing**: Every new feature or bug fix must be accompanied by corresponding tests using **PHPUnit**.
- **Code Coverage**: A minimum of **90% code coverage** is required for all new contributions.
- **Regression Testing**: Any bug fix must include a test case that fails without the fix and passes with it.

### 3.2 Automated Verification
Before any code is merged, it must pass:
- Standard PHPUnit test suites.
- Static analysis checks.

## 4. Contribution Workflow
- **Branching**: All work must be performed in dedicated `feature/` or `hotfix/` branches. Do not commit directly to the main branch.
- **Pull Requests**: Submit work via Pull Requests. Each PR should be concise and focused on a single concern.
- **Documentation**: Any changes to public APIs or core logic must be reflected in the project documentation.

## 5. Dependencies
The project relies on:
- `Teknoo/Immutable`
- `Teknoo/States`
- `Teknoo/Recipe`
