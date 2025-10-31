<?php

namespace MarketingPlanBundle\Tests\Repository;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\ConditionOperator;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Repository\NodeConditionRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;

/**
 * @internal
 */
#[CoversClass(NodeConditionRepository::class)]
#[RunTestsInSeparateProcesses]
final class NodeConditionRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 可以在这里添加测试初始化逻辑
    }

    protected function getRepository(): NodeConditionRepository
    {
        return self::getService(NodeConditionRepository::class);
    }

    public function testSave(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition = new NodeCondition();
        $condition->setName('Test Condition');
        $condition->setField('user_age');
        $condition->setOperator(ConditionOperator::GREATER_THAN);
        $condition->setValue('18');
        $condition->setNode($node);

        $this->getRepository()->save($condition, true);

        $this->assertGreaterThan(0, $condition->getId());
        $this->assertSame('Test Condition', $condition->getName());
        $this->assertSame('user_age', $condition->getField());
        $this->assertSame(ConditionOperator::GREATER_THAN, $condition->getOperator());
        $this->assertSame('18', $condition->getValue());
        $this->assertSame($node, $condition->getNode());
    }

    public function testRemove(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition = new NodeCondition();
        $condition->setName('Test Condition');
        $condition->setField('user_age');
        $condition->setOperator(ConditionOperator::GREATER_THAN);
        $condition->setValue('18');
        $condition->setNode($node);

        $this->getRepository()->save($condition, true);
        $conditionId = $condition->getId();

        $this->getRepository()->remove($condition, true);

        $removedCondition = $this->getRepository()->find($conditionId);
        $this->assertNull($removedCondition);
    }

    public function testFind(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition = new NodeCondition();
        $condition->setName('Test Condition');
        $condition->setField('user_age');
        $condition->setOperator(ConditionOperator::GREATER_THAN);
        $condition->setValue('18');
        $condition->setNode($node);

        $this->getRepository()->save($condition, true);
        $conditionId = $condition->getId();

        $foundCondition = $this->getRepository()->find($conditionId);
        $this->assertInstanceOf(NodeCondition::class, $foundCondition);
        $this->assertSame($conditionId, $foundCondition->getId());
        $this->assertSame('Test Condition', $foundCondition->getName());
    }

    public function testFindAll(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition1 = new NodeCondition();
        $condition1->setName('Condition 1');
        $condition1->setField('user_age');
        $condition1->setOperator(ConditionOperator::GREATER_THAN);
        $condition1->setValue('18');
        $condition1->setNode($node);
        $this->getRepository()->save($condition1, true);

        $condition2 = new NodeCondition();
        $condition2->setName('Condition 2');
        $condition2->setField('user_status');
        $condition2->setOperator(ConditionOperator::EQUAL);
        $condition2->setValue('active');
        $condition2->setNode($node);
        $this->getRepository()->save($condition2, true);

        $conditions = $this->getRepository()->findBy(['node' => $node]);
        $this->assertCount(2, $conditions);
        $this->assertContainsOnlyInstancesOf(NodeCondition::class, $conditions);
    }

    public function testFindBy(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition1 = new NodeCondition();
        $condition1->setName('Age Condition');
        $condition1->setField('user_age');
        $condition1->setOperator(ConditionOperator::GREATER_THAN);
        $condition1->setValue('18');
        $condition1->setNode($node);
        $this->getRepository()->save($condition1, true);

        $condition2 = new NodeCondition();
        $condition2->setName('Status Condition');
        $condition2->setField('user_status');
        $condition2->setOperator(ConditionOperator::EQUAL);
        $condition2->setValue('active');
        $condition2->setNode($node);
        $this->getRepository()->save($condition2, true);

        $ageConditions = $this->getRepository()->findBy(['field' => 'user_age']);
        $this->assertCount(1, $ageConditions);
        $this->assertSame('user_age', $ageConditions[0]->getField());

        $equalOperatorConditions = $this->getRepository()->findBy(['operator' => ConditionOperator::EQUAL]);
        $this->assertCount(1, $equalOperatorConditions);
        $this->assertSame(ConditionOperator::EQUAL, $equalOperatorConditions[0]->getOperator());
    }

    public function testFindByWithOrderAndLimit(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition1 = new NodeCondition();
        $condition1->setName('A Condition');
        $condition1->setField('field_a');
        $condition1->setOperator(ConditionOperator::EQUAL);
        $condition1->setValue('value_a');
        $condition1->setNode($node);
        $this->getRepository()->save($condition1, true);

        $condition2 = new NodeCondition();
        $condition2->setName('B Condition');
        $condition2->setField('field_b');
        $condition2->setOperator(ConditionOperator::EQUAL);
        $condition2->setValue('value_b');
        $condition2->setNode($node);
        $this->getRepository()->save($condition2, true);

        $conditions = $this->getRepository()->findBy(
            ['node' => $node],
            ['name' => 'DESC'],
            1,
            0
        );

        $this->assertCount(1, $conditions);
        $this->assertSame('B Condition', $conditions[0]->getName());
    }

    public function testFindOneBy(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition = new NodeCondition();
        $condition->setName('Unique Condition');
        $condition->setField('unique_field');
        $condition->setOperator(ConditionOperator::EQUAL);
        $condition->setValue('unique_value');
        $condition->setNode($node);
        $this->getRepository()->save($condition, true);

        $foundCondition = $this->getRepository()->findOneBy(['field' => 'unique_field']);
        $this->assertInstanceOf(NodeCondition::class, $foundCondition);
        $this->assertSame('unique_field', $foundCondition->getField());
        $this->assertSame('unique_value', $foundCondition->getValue());

        $notFoundCondition = $this->getRepository()->findOneBy(['field' => 'non_existent']);
        $this->assertNull($notFoundCondition);
    }

    public function testCount(): void
    {
        $initialCount = $this->getRepository()->count([]);

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition1 = new NodeCondition();
        $condition1->setName('Condition 1');
        $condition1->setField('field1');
        $condition1->setOperator(ConditionOperator::EQUAL);
        $condition1->setValue('value1');
        $condition1->setNode($node);
        $this->getRepository()->save($condition1, true);

        $condition2 = new NodeCondition();
        $condition2->setName('Condition 2');
        $condition2->setField('field2');
        $condition2->setOperator(ConditionOperator::GREATER_THAN);
        $condition2->setValue('value2');
        $condition2->setNode($node);
        $this->getRepository()->save($condition2, true);

        $totalCount = $this->getRepository()->count([]);
        $this->assertSame($initialCount + 2, $totalCount);

        $equalOperatorCount = $this->getRepository()->count(['operator' => ConditionOperator::EQUAL]);
        $this->assertSame(1, $equalOperatorCount);
    }

    public function testFindByNode(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('test1');
        $resourceConfig1->setAmount(1);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('test2');
        $resourceConfig2->setAmount(2);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        $condition1 = new NodeCondition();
        $condition1->setName('Condition 1');
        $condition1->setField('field1');
        $condition1->setOperator(ConditionOperator::EQUAL);
        $condition1->setValue('value1');
        $condition1->setNode($node1);
        $this->getRepository()->save($condition1, true);

        $condition2 = new NodeCondition();
        $condition2->setName('Condition 2');
        $condition2->setField('field2');
        $condition2->setOperator(ConditionOperator::GREATER_THAN);
        $condition2->setValue('value2');
        $condition2->setNode($node2);
        $this->getRepository()->save($condition2, true);

        $node1Conditions = $this->getRepository()->findBy(['node' => $node1]);
        $this->assertCount(1, $node1Conditions);
        $this->assertSame('Condition 1', $node1Conditions[0]->getName());

        $node2Conditions = $this->getRepository()->findBy(['node' => $node2]);
        $this->assertCount(1, $node2Conditions);
        $this->assertSame('Condition 2', $node2Conditions[0]->getName());
    }

    public function testFindByOperator(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition1 = new NodeCondition();
        $condition1->setName('Equal Condition');
        $condition1->setField('field1');
        $condition1->setOperator(ConditionOperator::EQUAL);
        $condition1->setValue('value1');
        $condition1->setNode($node);
        $this->getRepository()->save($condition1, true);

        $condition2 = new NodeCondition();
        $condition2->setName('Greater Than Condition');
        $condition2->setField('field2');
        $condition2->setOperator(ConditionOperator::GREATER_THAN);
        $condition2->setValue('value2');
        $condition2->setNode($node);
        $this->getRepository()->save($condition2, true);

        $equalConditions = $this->getRepository()->findBy(['node' => $node, 'operator' => ConditionOperator::EQUAL]);
        $this->assertCount(1, $equalConditions);
        $this->assertSame(ConditionOperator::EQUAL, $equalConditions[0]->getOperator());

        $greaterThanConditions = $this->getRepository()->findBy(['node' => $node, 'operator' => ConditionOperator::GREATER_THAN]);
        $this->assertCount(1, $greaterThanConditions);
        $this->assertSame(ConditionOperator::GREATER_THAN, $greaterThanConditions[0]->getOperator());
    }

    public function testFindByNullField(): void
    {
        $conditions = $this->getRepository()->findBy(['field' => null]);
        $this->assertIsArray($conditions);
        $this->assertEmpty($conditions);
    }

    public function testFindOneByAssociationNodeShouldReturnMatchingEntity(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('test');
        $resourceConfig->setAmount(1);

        $node = new Node();
        $node->setName('Test Node');
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $condition = new NodeCondition();
        $condition->setName('Test Condition');
        $condition->setField('test_field');
        $condition->setOperator(ConditionOperator::EQUAL);
        $condition->setValue('test_value');
        $condition->setNode($node);
        $this->getRepository()->save($condition, true);

        $found = $this->getRepository()->findOneBy(['node' => $node]);
        $this->assertInstanceOf(NodeCondition::class, $found);
        $this->assertSame($node, $found->getNode());
        $this->assertEquals('Test Condition', $found->getName());
    }

    public function testCountByAssociationNodeShouldReturnCorrectNumber(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('test1');
        $resourceConfig1->setAmount(1);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('test2');
        $resourceConfig2->setAmount(2);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        for ($i = 1; $i <= 4; ++$i) {
            $condition = new NodeCondition();
            $condition->setName('Node1 Condition ' . $i);
            $condition->setField('field' . $i);
            $condition->setOperator(ConditionOperator::EQUAL);
            $condition->setValue('value' . $i);
            $condition->setNode($node1);
            $this->getRepository()->save($condition, true);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $condition = new NodeCondition();
            $condition->setName('Node2 Condition ' . $i);
            $condition->setField('node2_field' . $i);
            $condition->setOperator(ConditionOperator::EQUAL);
            $condition->setValue('node2_value' . $i);
            $condition->setNode($node2);
            $this->getRepository()->save($condition, true);
        }

        $count = $this->getRepository()->count(['node' => $node1]);
        $this->assertSame(4, $count);
    }

    public function testFindByNodeAndField(): void
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig1 = new ResourceConfig();
        $resourceConfig1->setType('test1');
        $resourceConfig1->setAmount(1);

        $node1 = new Node();
        $node1->setName('Node 1');
        $node1->setType(NodeType::START);
        $node1->setTask($task);
        $node1->setResource($resourceConfig1);
        self::getEntityManager()->persist($node1);

        $resourceConfig2 = new ResourceConfig();
        $resourceConfig2->setType('test2');
        $resourceConfig2->setAmount(2);

        $node2 = new Node();
        $node2->setName('Node 2');
        $node2->setType(NodeType::RESOURCE);
        $node2->setTask($task);
        $node2->setResource($resourceConfig2);
        self::getEntityManager()->persist($node2);

        $condition1 = new NodeCondition();
        $condition1->setName('User Age Condition');
        $condition1->setField('user_age');
        $condition1->setOperator(ConditionOperator::GREATER_THAN);
        $condition1->setValue('18');
        $condition1->setNode($node1);
        $this->getRepository()->save($condition1, true);

        $condition2 = new NodeCondition();
        $condition2->setName('User Status Condition');
        $condition2->setField('user_status');
        $condition2->setOperator(ConditionOperator::EQUAL);
        $condition2->setValue('active');
        $condition2->setNode($node1);
        $this->getRepository()->save($condition2, true);

        $condition3 = new NodeCondition();
        $condition3->setName('Another Age Condition');
        $condition3->setField('user_age');
        $condition3->setOperator(ConditionOperator::LESS_THAN);
        $condition3->setValue('65');
        $condition3->setNode($node2);
        $this->getRepository()->save($condition3, true);

        $results = $this->getRepository()->findByNodeAndField((string) $node1->getId(), 'user_age');
        $this->assertCount(1, $results);
        $this->assertSame('User Age Condition', $results[0]->getName());
        $this->assertSame('user_age', $results[0]->getField());
        $this->assertSame($node1, $results[0]->getNode());

        $results = $this->getRepository()->findByNodeAndField((string) $node2->getId(), 'user_age');
        $this->assertCount(1, $results);
        $this->assertSame('Another Age Condition', $results[0]->getName());

        $results = $this->getRepository()->findByNodeAndField((string) $node1->getId(), 'nonexistent_field');
        $this->assertCount(0, $results);
    }

    protected function createNewEntity(): object
    {
        $task = new Task();
        $task->setTitle('Test Task ' . uniqid());
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+7 days'));
        $crowd = new Tag();
        $crowd->setName('test-tag-' . uniqid());
        $task->setCrowd($crowd);
        self::getEntityManager()->persist($crowd);
        self::getEntityManager()->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node ' . uniqid());
        $node->setType(NodeType::START);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        self::getEntityManager()->persist($node);

        $entity = new NodeCondition();
        $entity->setName('Test Condition ' . uniqid());
        $entity->setField('test_field');
        $entity->setOperator(ConditionOperator::EQUALS);
        $entity->setValue('test_value');
        $entity->setNode($node);

        return $entity;
    }
}
