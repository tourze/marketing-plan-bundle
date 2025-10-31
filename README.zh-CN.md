# Marketing Plan Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![PHP Version](https://img.shields.io/badge/php-%E2%89%A58.1-blue.svg?style=flat-square)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg?style=flat-square)](#)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg?style=flat-square)](#)

一个功能完整的 Symfony 包，用于创建和管理基于流程节点的营销自动化活动，支持用户进度跟踪和条件判断逻辑。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [依赖项](#依赖项)
- [配置](#配置)
- [快速开始](#快速开始)
- [高级使用](#高级使用)
- [控制台命令](#控制台命令)
- [测试](#测试)
- [许可证](#许可证)

## 功能特性

- **基于流程的营销活动**: 使用不同节点类型创建复杂的营销流程
- **节点管理**: 支持开始、延时、条件、资源分发和结束节点
- **用户进度跟踪**: 监控用户在营销活动中的行为轨迹
- **条件判断逻辑**: 定义流程控制和用户分群的条件
- **延时管理**: 配置基于时间的延迟和调度
- **自动状态管理**: 自动化任务生命周期管理
- **管理界面**: 集成 EasyAdmin 的活动管理界面
- **控制台命令**: 内置后台处理命令

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 3.0 或更高版本

## 安装

```bash
composer require tourze/marketing-plan-bundle
```

## 依赖项

此包依赖以下 tourze 包：

- `tourze/doctrine-indexed-bundle`: 数据库索引功能
- `tourze/doctrine-snowflake-bundle`: 唯一 ID 生成
- `tourze/doctrine-timestamp-bundle`: 自动时间戳
- `tourze/doctrine-track-bundle`: 实体跟踪
- `tourze/enum-extra`: 增强枚举功能
- `tourze/resource-manage-bundle`: 资源管理

## 配置

将包添加到 `config/bundles.php`：

```php
return [
    // ...
    MarketingPlanBundle\MarketingPlanBundle::class => ['all' => true],
];
```

运行数据库迁移配置数据结构：

```bash
php bin/console doctrine:migrations:migrate
```

## 快速开始

### 创建营销任务

```php
<?php

use MarketingPlanBundle\Service\TaskService;
use MarketingPlanBundle\Service\NodeService;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\DelayType;

// 通过依赖注入使用服务
class MarketingController
{
    public function __construct(
        private TaskService $taskService,
        private NodeService $nodeService,
    ) {}

    public function createWelcomeCampaign()
    {
        // 创建新的营销任务
        $task = $this->taskService->create(
            title: '欢迎活动',
            crowd: $userTag, // 您的用户标签实现
            startTime: new \DateTime('2024-01-01'),
            endTime: new \DateTime('2024-12-31')
        );

        // 添加欢迎邮件节点
        $emailNode = $this->nodeService->create($task, '发送欢迎邮件', NodeType::RESOURCE);
        
        // 添加延时节点（等待3天）
        $delayNode = $this->nodeService->create($task, '等待3天', NodeType::DELAY);
        $this->nodeService->addDelay($delayNode, DelayType::DAYS, 3);

        // 添加条件节点
        $conditionNode = $this->nodeService->create($task, '检查邮件打开', NodeType::CONDITION);
        $this->nodeService->addCondition($conditionNode, 'email_opened', \MarketingPlanBundle\Enum\ConditionOperator::EQUALS, 'true');

        // 发布任务
        $this->taskService->publish($task);
    }
}
```

### 管理用户进度

```php
use MarketingPlanBundle\Service\UserProgressService;

class UserJourneyController
{
    public function __construct(
        private UserProgressService $progressService,
    ) {}

    public function startUserJourney(string $userId, Task $task)
    {
        // 开始用户流程
        $progress = $this->progressService->start($userId, $task);
        
        // 将用户移动到下一个节点
        $this->progressService->proceed($progress);
        
        // 检查用户状态
        if ($this->progressService->isCompleted($progress)) {
            // 用户完成了活动
        }
    }
}
```

## 高级使用

### 自定义节点类型

包支持五种节点类型：

- **START**: 营销流程的入口点
- **DELAY**: 基于时间的延迟（天、小时、特定时间）
- **CONDITION**: 流程控制的条件逻辑
- **RESOURCE**: 资源分发（邮件、通知、奖励）
- **END**: 营销流程的出口点

### 延时配置

```php
// 等待7天
$this->nodeService->addDelay($node, DelayType::DAYS, 7);

// 等待2小时
$this->nodeService->addDelay($node, DelayType::HOURS, 2);

// 等到特定时间
$this->nodeService->addDelay($node, DelayType::SPECIFIC_TIME, null, new \DateTimeImmutable('2024-01-15 09:00:00'));
```

### 条件配置

```php
use MarketingPlanBundle\Enum\ConditionOperator;

// 检查用户是否打开邮件
$this->nodeService->addCondition($node, 'email_opened', ConditionOperator::EQUALS, 'true');

// 检查用户积分大于100
$this->nodeService->addCondition($node, 'user_score', ConditionOperator::GREATER_THAN, '100');

// 检查用户是否有特定标签
$this->nodeService->addCondition($node, 'has_tag', ConditionOperator::CONTAINS, 'premium');
```

## 控制台命令

### 检查节点超时

自动检查和处理节点超时：

```bash
php bin/console marketing-plan:check-node-timeout
```

该命令的功能：
- 识别超过延时超时的用户
- 将用户标记为流失并记录适当的原因
- 更新用户进度状态

### 检查任务状态

自动管理任务生命周期：

```bash
php bin/console marketing-plan:check-task-status
```

该命令的功能：
- 在到达开始时间时启动任务
- 在到达结束时间时结束任务
- 自动更新任务状态

## 测试

该包包含涵盖所有组件的完整单元测试：

```bash
./vendor/bin/phpunit packages/marketing-plan-bundle/tests
```

测试覆盖包括：
- 实体验证和关系
- 服务层功能
- 控制台命令行为
- 仓库方法
- 枚举功能

详细测试信息请参考 [TEST_PLAN.md](./TEST_PLAN.md)。

## 许可证

本包是在 [MIT许可证](https://opensource.org/licenses/MIT) 下发布的开源软件。
