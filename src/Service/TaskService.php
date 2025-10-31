<?php

namespace MarketingPlanBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Exception\TaskException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use Tourze\UserTagContracts\TagInterface;

#[Autoconfigure(public: true)]
class TaskService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 创建任务
     * 1. 创建任务记录
     * 2. 创建开始和结束节点
     */
    public function create(string $title, TagInterface $crowd, \DateTimeInterface $startTime, \DateTimeInterface $endTime): Task
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setCrowd($crowd);
        $task->setStartTime(\DateTimeImmutable::createFromInterface($startTime));
        $task->setEndTime(\DateTimeImmutable::createFromInterface($endTime));
        $task->setStatus(TaskStatus::DRAFT);

        // 创建开始节点资源配置
        $startResourceConfig = new ResourceConfig();
        $startResourceConfig->setType('none');
        $startResourceConfig->setAmount(0);

        // 创建开始节点
        $startNode = new Node();
        $startNode->setName('开始');
        $startNode->setType(NodeType::START);
        $startNode->setSequence(1);
        $startNode->setTask($task);
        $startNode->setResource($startResourceConfig);

        // 创建结束节点资源配置
        $endResourceConfig = new ResourceConfig();
        $endResourceConfig->setType('none');
        $endResourceConfig->setAmount(0);

        // 创建结束节点
        $endNode = new Node();
        $endNode->setName('结束');
        $endNode->setType(NodeType::END);
        $endNode->setSequence(999);
        $endNode->setTask($task);
        $endNode->setResource($endResourceConfig);

        // 将节点添加到任务
        $task->addNode($startNode);
        $task->addNode($endNode);

        $this->entityManager->persist($task);
        $this->entityManager->persist($startNode);
        $this->entityManager->persist($endNode);
        $this->entityManager->flush();

        return $task;
    }

    /**
     * 发布任务
     * 1. 检查任务配置是否完整
     * 2. 更新任务状态
     */
    public function publish(Task $task): void
    {
        // 检查是否有开始和结束节点
        $hasStart = false;
        $hasEnd = false;
        foreach ($task->getNodes() as $node) {
            if (NodeType::START === $node->getType()) {
                $hasStart = true;
            }
            if (NodeType::END === $node->getType()) {
                $hasEnd = true;
            }
        }

        if (!$hasStart || !$hasEnd) {
            throw new TaskException('Task must have START and END nodes');
        }

        // 检查节点顺序是否连续
        $sequences = $task->getNodes()
            ->map(fn (Node $node) => $node->getSequence())
            ->toArray()
        ;
        sort($sequences);
        $expected = range(1, count($sequences));
        if ($sequences !== $expected) {
            throw new TaskException('Node sequences must be continuous');
        }

        $task->setStatus(TaskStatus::RUNNING);
        $this->entityManager->flush();
    }

    /**
     * 暂停任务
     */
    public function pause(Task $task): void
    {
        if (TaskStatus::RUNNING !== $task->getStatus()) {
            throw new TaskException('Task is not running');
        }

        $task->setStatus(TaskStatus::PAUSED);
        $this->entityManager->flush();
    }

    /**
     * 恢复任务
     */
    public function resume(Task $task): void
    {
        if (TaskStatus::PAUSED !== $task->getStatus()) {
            throw new TaskException('Task is not paused');
        }

        $task->setStatus(TaskStatus::RUNNING);
        $this->entityManager->flush();
    }

    /**
     * 结束任务
     */
    public function finish(Task $task): void
    {
        if (TaskStatus::RUNNING !== $task->getStatus()) {
            throw new TaskException('Task is not running');
        }

        $task->setStatus(TaskStatus::FINISHED);
        $this->entityManager->flush();
    }

    /**
     * 检查任务状态
     * 1. 检查是否到达开始时间
     * 2. 检查是否到达结束时间
     */
    public function checkStatus(Task $task): void
    {
        $now = new \DateTime();

        // 如果未开始，检查是否需要开始
        if (TaskStatus::DRAFT === $task->getStatus() && $now >= $task->getStartTime()) {
            $this->publish($task);

            return;
        }

        // 如果运行中，检查是否需要结束
        if (TaskStatus::RUNNING === $task->getStatus() && $now >= $task->getEndTime()) {
            $this->finish($task);

            return;
        }
    }
}
