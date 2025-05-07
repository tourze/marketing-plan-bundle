<?php

namespace MarketingPlanBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\ConditionOperator;
use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Repository\NodeRepository;

class NodeService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly NodeRepository $nodeRepository,
    ) {
    }

    /**
     * 创建节点
     */
    public function create(Task $task, string $name, NodeType $type): Node
    {
        // 计算序号
        $maxSequence = $task->getNodes()
            ->map(fn (Node $node) => $node->getSequence())
            ->max();

        $node = new Node();
        $node->setName($name)
            ->setType($type)
            ->setSequence($maxSequence + 1)
            ->setTask($task);

        $this->entityManager->persist($node);
        $this->entityManager->flush();

        return $node;
    }

    /**
     * 添加条件
     */
    public function addCondition(Node $node, string $name, string $field, ConditionOperator $operator, string $value): NodeCondition
    {
        $condition = new NodeCondition();
        $condition->setName($name)
            ->setField($field)
            ->setOperator($operator)
            ->setValue($value)
            ->setNode($node);

        $this->entityManager->persist($condition);
        $this->entityManager->flush();

        return $condition;
    }

    /**
     * 设置延时
     */
    public function setDelay(Node $node, DelayType $type, string $value): NodeDelay
    {
        $delay = $node->getDelay() ?? new NodeDelay();
        $delay->setType($type)
            ->setValue($value)
            ->setNode($node);

        if (null === $node->getDelay()) {
            $this->entityManager->persist($delay);
        }

        $this->entityManager->flush();

        return $delay;
    }

    /**
     * 调整节点顺序
     * 1. 检查序号是否合法
     * 2. 更新节点序号
     */
    public function updateSequence(Node $node, int $sequence): void
    {
        $task = $node->getTask();

        // 检查序号是否合法
        if ($sequence < 1) {
            throw new \InvalidArgumentException('Sequence must be greater than 0');
        }

        $maxSequence = $task->getNodes()
            ->map(fn (Node $node) => $node->getSequence())
            ->max();

        if ($sequence > $maxSequence) {
            throw new \InvalidArgumentException('Sequence must be less than or equal to ' . $maxSequence);
        }

        // 如果是开始节点，必须是第一个
        if (NodeType::START === $node->getType() && 1 !== $sequence) {
            throw new \InvalidArgumentException('START node must be the first node');
        }

        // 如果是结束节点，必须是最后一个
        if (NodeType::END === $node->getType() && $sequence !== $maxSequence) {
            throw new \InvalidArgumentException('END node must be the last node');
        }

        // 更新序号
        $oldSequence = $node->getSequence();
        if ($sequence > $oldSequence) {
            // 向后移动，中间的节点序号-1
            foreach ($task->getNodes() as $otherNode) {
                if ($otherNode->getSequence() > $oldSequence && $otherNode->getSequence() <= $sequence) {
                    $otherNode->setSequence($otherNode->getSequence() - 1);
                }
            }
        } else {
            // 向前移动，中间的节点序号+1
            foreach ($task->getNodes() as $otherNode) {
                if ($otherNode->getSequence() >= $sequence && $otherNode->getSequence() < $oldSequence) {
                    $otherNode->setSequence($otherNode->getSequence() + 1);
                }
            }
        }

        $node->setSequence($sequence);
        $this->entityManager->flush();
    }

    /**
     * 删除节点
     * 1. 检查是否可以删除
     * 2. 更新其他节点的序号
     * 3. 删除节点
     */
    public function delete(Node $node): void
    {
        // 检查是否可以删除
        if (NodeType::START === $node->getType()) {
            throw new \RuntimeException('Cannot delete START node');
        }

        if (NodeType::END === $node->getType()) {
            throw new \RuntimeException('Cannot delete END node');
        }

        $sequence = $node->getSequence();
        $task = $node->getTask();

        // 更新其他节点的序号
        foreach ($task->getNodes() as $otherNode) {
            if ($otherNode->getSequence() > $sequence) {
                $otherNode->setSequence($otherNode->getSequence() - 1);
            }
        }

        $this->entityManager->remove($node);
        $this->entityManager->flush();
    }
}
