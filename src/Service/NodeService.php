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
use MarketingPlanBundle\Exception\InvalidArgumentException;
use MarketingPlanBundle\Exception\NodeException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

#[Autoconfigure(public: true)]
class NodeService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 创建节点
     */
    public function create(Task $task, string $name, NodeType $type): Node
    {
        // 计算序号
        $sequences = $task->getNodes()
            ->map(fn (Node $node) => $node->getSequence())
            ->toArray()
        ;
        $maxSequence = count($sequences) > 0 ? max($sequences) : 0;

        // 创建默认的 ResourceConfig
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName($name);
        $node->setType($type);
        $node->setSequence($maxSequence + 1);
        $node->setTask($task);
        $node->setResource($resourceConfig);

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
        $condition->setName($name);
        $condition->setField($field);
        $condition->setOperator($operator);
        $condition->setValue($value);
        $condition->setNode($node);

        $this->entityManager->persist($condition);
        $this->entityManager->flush();

        return $condition;
    }

    /**
     * 设置延时
     *
     * @phpstan-ignore-next-line symplify.noReturnSetterMethod
     */
    public function setDelay(Node $node, DelayType $type, string $value): NodeDelay
    {
        $delay = $node->getDelay() ?? new NodeDelay();
        $delay->setType($type);
        $delay->setValue((int) $value);
        $delay->setNode($node);

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
        if (null === $task) {
            throw new InvalidArgumentException('Node must be associated with a task');
        }
        $this->validateSequence($node, $sequence, $task);

        $oldSequence = $node->getSequence();
        if ($sequence !== $oldSequence) {
            $this->shiftNodeSequences($task, $oldSequence, $sequence);
            $node->setSequence($sequence);
            $this->entityManager->flush();
        }
    }

    private function validateSequence(Node $node, int $sequence, Task $task): void
    {
        if ($sequence < 1) {
            throw new InvalidArgumentException('Sequence must be greater than 0');
        }

        $maxSequence = $this->getMaxSequence($task);

        if ($sequence > $maxSequence) {
            throw new InvalidArgumentException('Sequence must be less than or equal to ' . $maxSequence);
        }

        $this->validateSpecialNodeTypes($node, $sequence, $maxSequence);
    }

    private function getMaxSequence(Task $task): int
    {
        $sequences = $task->getNodes()
            ->map(fn (Node $node) => $node->getSequence())
            ->toArray()
        ;

        return count($sequences) > 0 ? max($sequences) : 0;
    }

    private function validateSpecialNodeTypes(Node $node, int $sequence, int $maxSequence): void
    {
        if (NodeType::START === $node->getType() && 1 !== $sequence) {
            throw new InvalidArgumentException('START node must be the first node');
        }

        if (NodeType::END === $node->getType() && $sequence !== $maxSequence) {
            throw new InvalidArgumentException('END node must be the last node');
        }
    }

    private function shiftNodeSequences(Task $task, int $oldSequence, int $newSequence): void
    {
        if ($newSequence > $oldSequence) {
            $this->shiftNodesBackward($task, $oldSequence, $newSequence);
        } else {
            $this->shiftNodesForward($task, $oldSequence, $newSequence);
        }
    }

    private function shiftNodesBackward(Task $task, int $oldSequence, int $newSequence): void
    {
        foreach ($task->getNodes() as $otherNode) {
            if ($otherNode->getSequence() > $oldSequence && $otherNode->getSequence() <= $newSequence) {
                $otherNode->setSequence($otherNode->getSequence() - 1);
            }
        }
    }

    private function shiftNodesForward(Task $task, int $oldSequence, int $newSequence): void
    {
        foreach ($task->getNodes() as $otherNode) {
            if ($otherNode->getSequence() >= $newSequence && $otherNode->getSequence() < $oldSequence) {
                $otherNode->setSequence($otherNode->getSequence() + 1);
            }
        }
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
            throw new NodeException('Cannot delete START node');
        }

        if (NodeType::END === $node->getType()) {
            throw new NodeException('Cannot delete END node');
        }

        $sequence = $node->getSequence();
        $task = $node->getTask();
        if (null === $task) {
            throw new InvalidArgumentException('Node must be associated with a task');
        }

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
