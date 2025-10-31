# Marketing Plan Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/badge/php-%E2%89%A58.1-blue.svg?style=flat-square)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg?style=flat-square)](#)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg?style=flat-square)](#)

A comprehensive Symfony bundle for creating and managing marketing automation campaigns with flow-based node systems, user progress tracking, and conditional logic.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Dependencies](#dependencies)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Advanced Usage](#advanced-usage)
- [Console Commands](#console-commands)
- [Testing](#testing)
- [License](#license)

## Features

- **Flow-based Marketing Campaigns**: Create complex marketing flows with different node types
- **Node Management**: Support for START, DELAY, CONDITION, RESOURCE, and END nodes
- **User Progress Tracking**: Monitor user journey through marketing campaigns
- **Conditional Logic**: Define conditions for flow control and user segmentation
- **Delay Management**: Configure time-based delays and scheduling
- **Automated Status Management**: Automatic task lifecycle management
- **Admin Interface**: EasyAdmin integration for campaign management
- **Console Commands**: Built-in commands for background processing

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 3.0 or higher

## Installation

```bash
composer require tourze/marketing-plan-bundle
```

## Dependencies

This bundle depends on several other tourze packages:

- `tourze/doctrine-indexed-bundle`: For database indexing
- `tourze/doctrine-snowflake-bundle`: For unique ID generation
- `tourze/doctrine-timestamp-bundle`: For automatic timestamps
- `tourze/doctrine-track-bundle`: For entity tracking
- `tourze/enum-extra`: For enhanced enum functionality
- `tourze/resource-manage-bundle`: For resource management

## Configuration

Add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    MarketingPlanBundle\MarketingPlanBundle::class => ['all' => true],
];
```

Configure the database schema by running migrations:

```bash
php bin/console doctrine:migrations:migrate
```

## Quick Start

### Creating a Marketing Task

```php
<?php

use MarketingPlanBundle\Service\TaskService;
use MarketingPlanBundle\Service\NodeService;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\DelayType;

// Inject services via dependency injection
class MarketingController
{
    public function __construct(
        private TaskService $taskService,
        private NodeService $nodeService,
    ) {}

    public function createWelcomeCampaign()
    {
        // Create a new marketing task
        $task = $this->taskService->create(
            title: 'Welcome Campaign',
            crowd: $userTag, // Your user tag implementation
            startTime: new \DateTime('2024-01-01'),
            endTime: new \DateTime('2024-12-31')
        );

        // Add a welcome email node
        $emailNode = $this->nodeService->create($task, 'Send Welcome Email', NodeType::RESOURCE);
        
        // Add a delay node (wait 3 days)
        $delayNode = $this->nodeService->create($task, 'Wait 3 Days', NodeType::DELAY);
        $this->nodeService->addDelay($delayNode, DelayType::DAYS, 3);

        // Add a condition node
        $conditionNode = $this->nodeService->create($task, 'Check Email Open', NodeType::CONDITION);
        $this->nodeService->addCondition($conditionNode, 'email_opened', \MarketingPlanBundle\Enum\ConditionOperator::EQUALS, 'true');

        // Publish the task
        $this->taskService->publish($task);
    }
}
```

### Managing User Progress

```php
use MarketingPlanBundle\Service\UserProgressService;

class UserJourneyController
{
    public function __construct(
        private UserProgressService $progressService,
    ) {}

    public function startUserJourney(string $userId, Task $task)
    {
        // Start user journey
        $progress = $this->progressService->start($userId, $task);
        
        // Move user to next node
        $this->progressService->proceed($progress);
        
        // Check user status
        if ($this->progressService->isCompleted($progress)) {
            // User completed the campaign
        }
    }
}
```

## Advanced Usage

### Custom Node Types

The bundle supports five node types:

- **START**: Entry point of the marketing flow
- **DELAY**: Time-based delays (days, hours, specific time)
- **CONDITION**: Conditional logic for flow control
- **RESOURCE**: Resource delivery (emails, notifications, rewards)
- **END**: Exit point of the marketing flow

### Delay Configuration

```php
// Wait for 7 days
$this->nodeService->addDelay($node, DelayType::DAYS, 7);

// Wait for 2 hours
$this->nodeService->addDelay($node, DelayType::HOURS, 2);

// Wait until specific time
$this->nodeService->addDelay($node, DelayType::SPECIFIC_TIME, null, new \DateTimeImmutable('2024-01-15 09:00:00'));
```

### Condition Configuration

```php
use MarketingPlanBundle\Enum\ConditionOperator;

// Check if user opened email
$this->nodeService->addCondition($node, 'email_opened', ConditionOperator::EQUALS, 'true');

// Check user score greater than 100
$this->nodeService->addCondition($node, 'user_score', ConditionOperator::GREATER_THAN, '100');

// Check if user has specific tag
$this->nodeService->addCondition($node, 'has_tag', ConditionOperator::CONTAINS, 'premium');
```

## Console Commands

### Check Node Timeouts

Automatically check and process node timeouts:

```bash
php bin/console marketing-plan:check-node-timeout
```

This command:
- Identifies users who have exceeded delay timeouts
- Marks users as dropped with appropriate reasons
- Updates user progress status

### Check Task Status

Automatically manage task lifecycle:

```bash
php bin/console marketing-plan:check-task-status
```

This command:
- Starts tasks when their start time is reached
- Ends tasks when their end time is reached
- Updates task status automatically

## Testing

The bundle includes comprehensive unit tests covering all components:

```bash
./vendor/bin/phpunit packages/marketing-plan-bundle/tests
```

Test coverage includes:
- Entity validation and relationships
- Service layer functionality
- Console command behavior
- Repository methods
- Enum functionality

For detailed test information, see [TEST_PLAN.md](./TEST_PLAN.md).

## License

This bundle is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
