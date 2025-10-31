<?php

namespace MarketingPlanBundle\Tests\Service;

use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\ConditionOperator;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Exception\InvalidArgumentException;
use MarketingPlanBundle\Exception\NodeException;
use MarketingPlanBundle\Service\NodeService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;
use UserTagBundle\Enum\TagType;

/**
 * @internal
 */
#[CoversClass(NodeService::class)]
#[RunTestsInSeparateProcesses]
final class NodeServiceTest extends AbstractIntegrationTestCase
{
    private NodeService $nodeService;

    protected function onSetUp(): void
    {
        $service = self::getContainer()->get(NodeService::class);
        $this->assertInstanceOf(NodeService::class, $service);
        $this->nodeService = $service;
    }

    public function testNodeServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(NodeService::class, $this->nodeService);
    }

    public function testCreateCreatesNodeWithCorrectSequence(): void
    {
        $task = $this->createTask();
        self::getEntityManager()->persist($task);
        self::getEntityManager()->flush();

        $node = $this->nodeService->create($task, 'Test Node', NodeType::RESOURCE);

        // Set required ResourceConfig for RESOURCE type nodes
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('points');
        $resourceConfig->setAmount(100);
        $node->setResource($resourceConfig);
        self::getEntityManager()->flush();

        $this->assertInstanceOf(Node::class, $node);
        $this->assertSame('Test Node', $node->getName());
        $this->assertSame(NodeType::RESOURCE, $node->getType());
        $this->assertSame($task, $node->getTask());
        $this->assertSame(1, $node->getSequence());
        $this->assertNotNull($node->getId());
    }

    public function testCreateAssignsCorrectSequenceWithExistingNodes(): void
    {
        $task = $this->createTask();
        self::getEntityManager()->persist($task);

        // Create existing node
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $existingNode = new Node();
        $existingNode->setName('Existing');
        $existingNode->setType(NodeType::START);
        $existingNode->setSequence(1);
        $existingNode->setTask($task);
        $existingNode->setResource($resourceConfig);
        $task->addNode($existingNode);
        self::getEntityManager()->persist($existingNode);
        self::getEntityManager()->flush();

        $newNode = $this->nodeService->create($task, 'New Node', NodeType::RESOURCE);

        // Set required ResourceConfig for RESOURCE type nodes
        $newResourceConfig = new ResourceConfig();
        $newResourceConfig->setType('points');
        $newResourceConfig->setAmount(100);
        $newNode->setResource($newResourceConfig);
        self::getEntityManager()->flush();

        $this->assertSame(2, $newNode->getSequence());
    }

    public function testCreateHandlesMultipleExistingNodes(): void
    {
        $task = $this->createTask();
        self::getEntityManager()->persist($task);

        // Create multiple existing nodes
        for ($i = 1; $i <= 3; ++$i) {
            $resourceConfig = new ResourceConfig();
            $resourceConfig->setType('none');
            $resourceConfig->setAmount(0);

            $node = new Node();
            $node->setName('Node ' . $i);
            $node->setType(NodeType::RESOURCE);
            $node->setSequence($i);
            $node->setTask($task);
            $node->setResource($resourceConfig);
            $task->addNode($node);
            self::getEntityManager()->persist($node);
        }
        self::getEntityManager()->flush();

        $newNode = $this->nodeService->create($task, 'New Node', NodeType::DELAY);

        $this->assertSame(4, $newNode->getSequence());
    }

    public function testAddConditionCreatesNodeCondition(): void
    {
        $task = $this->createTask();
        $node = $this->createNode($task, 'Test Node', NodeType::CONDITION);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        $condition = $this->nodeService->addCondition(
            $node,
            'Age Check',
            'age',
            ConditionOperator::GREATER_THAN,
            '18'
        );

        $this->assertNotNull($condition->getId());
        $this->assertSame('Age Check', $condition->getName());
        $this->assertSame('age', $condition->getField());
        $this->assertSame(ConditionOperator::GREATER_THAN, $condition->getOperator());
        $this->assertSame('18', $condition->getValue());
        $this->assertSame($node, $condition->getNode());
    }

    public function testAddConditionWithDifferentOperators(): void
    {
        $task = $this->createTask();
        $node = $this->createNode($task, 'Test Node', NodeType::CONDITION);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        $condition = $this->nodeService->addCondition(
            $node,
            'Status Check',
            'status',
            ConditionOperator::EQUAL,
            'active'
        );

        $this->assertSame(ConditionOperator::EQUAL, $condition->getOperator());
        $this->assertSame('active', $condition->getValue());
    }

    public function testDeleteRemovesNodeAndUpdatesSequences(): void
    {
        $task = $this->createTask();
        self::getEntityManager()->persist($task);

        // Create nodes with sequences 1, 2, 3
        $node1 = $this->createNode($task, 'Node 1', NodeType::RESOURCE, 1);
        $node2 = $this->createNode($task, 'Node 2', NodeType::RESOURCE, 2);
        $node3 = $this->createNode($task, 'Node 3', NodeType::RESOURCE, 3);

        self::getEntityManager()->persist($node1);
        self::getEntityManager()->persist($node2);
        self::getEntityManager()->persist($node3);
        self::getEntityManager()->flush();

        $node2Id = $node2->getId();

        // Delete middle node
        $this->nodeService->delete($node2);

        // Verify node is deleted
        $deletedNode = self::getEntityManager()->find(Node::class, $node2Id);
        $this->assertNull($deletedNode);

        // Verify sequences are updated
        self::getEntityManager()->refresh($node3);
        $this->assertSame(2, $node3->getSequence());
    }

    public function testDeleteThrowsExceptionForStartNode(): void
    {
        $task = $this->createTask();
        $startNode = $this->createNode($task, 'Start', NodeType::START);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->persist($startNode);
        self::getEntityManager()->flush();

        $this->expectException(NodeException::class);
        $this->expectExceptionMessage('Cannot delete START node');

        $this->nodeService->delete($startNode);
    }

    public function testDeleteThrowsExceptionForEndNode(): void
    {
        $task = $this->createTask();
        $endNode = $this->createNode($task, 'End', NodeType::END);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->persist($endNode);
        self::getEntityManager()->flush();

        $this->expectException(NodeException::class);
        $this->expectExceptionMessage('Cannot delete END node');

        $this->nodeService->delete($endNode);
    }

    public function testUpdateSequenceMovesNodeForward(): void
    {
        $task = $this->createTask();
        self::getEntityManager()->persist($task);

        // Create nodes with sequences 1, 2, 3, 4
        $node1 = $this->createNode($task, 'Node 1', NodeType::START, 1);
        $node2 = $this->createNode($task, 'Node 2', NodeType::RESOURCE, 2);
        $node3 = $this->createNode($task, 'Node 3', NodeType::DELAY, 3);
        $node4 = $this->createNode($task, 'Node 4', NodeType::END, 4);

        self::getEntityManager()->persist($node1);
        self::getEntityManager()->persist($node2);
        self::getEntityManager()->persist($node3);
        self::getEntityManager()->persist($node4);
        self::getEntityManager()->flush();

        // Move node 2 to position 3
        $this->nodeService->updateSequence($node2, 3);

        self::getEntityManager()->refresh($node1);
        self::getEntityManager()->refresh($node2);
        self::getEntityManager()->refresh($node3);
        self::getEntityManager()->refresh($node4);

        $this->assertSame(1, $node1->getSequence());
        $this->assertSame(3, $node2->getSequence());
        $this->assertSame(2, $node3->getSequence());
        $this->assertSame(4, $node4->getSequence());
    }

    public function testUpdateSequenceMovesNodeBackward(): void
    {
        $task = $this->createTask();
        self::getEntityManager()->persist($task);

        // Create nodes with sequences 1, 2, 3, 4
        $node1 = $this->createNode($task, 'Node 1', NodeType::START, 1);
        $node2 = $this->createNode($task, 'Node 2', NodeType::RESOURCE, 2);
        $node3 = $this->createNode($task, 'Node 3', NodeType::DELAY, 3);
        $node4 = $this->createNode($task, 'Node 4', NodeType::END, 4);

        self::getEntityManager()->persist($node1);
        self::getEntityManager()->persist($node2);
        self::getEntityManager()->persist($node3);
        self::getEntityManager()->persist($node4);
        self::getEntityManager()->flush();

        // Move node 3 to position 2
        $this->nodeService->updateSequence($node3, 2);

        self::getEntityManager()->refresh($node1);
        self::getEntityManager()->refresh($node2);
        self::getEntityManager()->refresh($node3);
        self::getEntityManager()->refresh($node4);

        $this->assertSame(1, $node1->getSequence());
        $this->assertSame(3, $node2->getSequence());
        $this->assertSame(2, $node3->getSequence());
        $this->assertSame(4, $node4->getSequence());
    }

    public function testUpdateSequenceDoesNothingWhenSameSequence(): void
    {
        $task = $this->createTask();
        $node = $this->createNode($task, 'Node', NodeType::RESOURCE, 2);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        $this->nodeService->updateSequence($node, 2);

        $this->assertSame(2, $node->getSequence());
    }

    public function testUpdateSequenceThrowsExceptionForInvalidSequence(): void
    {
        $task = $this->createTask();
        $node = $this->createNode($task, 'Node', NodeType::RESOURCE, 1);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sequence must be greater than 0');

        $this->nodeService->updateSequence($node, 0);
    }

    public function testUpdateSequenceThrowsExceptionForTooHighSequence(): void
    {
        $task = $this->createTask();
        $node = $this->createNode($task, 'Node', NodeType::RESOURCE, 1);
        self::getEntityManager()->persist($task);
        self::getEntityManager()->persist($node);
        self::getEntityManager()->flush();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sequence must be less than or equal to 1');

        $this->nodeService->updateSequence($node, 5);
    }

    public function testUpdateSequenceThrowsExceptionForStartNodeNotFirst(): void
    {
        $task = $this->createTask();
        self::getEntityManager()->persist($task);

        $startNode = $this->createNode($task, 'Start', NodeType::START, 1);
        $resourceNode = $this->createNode($task, 'Resource', NodeType::RESOURCE, 2);

        self::getEntityManager()->persist($startNode);
        self::getEntityManager()->persist($resourceNode);
        self::getEntityManager()->flush();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('START node must be the first node');

        $this->nodeService->updateSequence($startNode, 2);
    }

    public function testUpdateSequenceThrowsExceptionForEndNodeNotLast(): void
    {
        $task = $this->createTask();
        self::getEntityManager()->persist($task);

        $startNode = $this->createNode($task, 'Start', NodeType::START, 1);
        $endNode = $this->createNode($task, 'End', NodeType::END, 2);

        self::getEntityManager()->persist($startNode);
        self::getEntityManager()->persist($endNode);
        self::getEntityManager()->flush();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('END node must be the last node');

        $this->nodeService->updateSequence($endNode, 1);
    }

    private function createTask(): Task
    {
        $task = new Task();
        $task->setTitle('Test Task');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));

        $crowd = $this->createTag('Test Tag');
        $task->setCrowd($crowd);

        return $task;
    }

    private function createNode(Task $task, string $name, NodeType $type, int $sequence = 1): Node
    {
        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName($name);
        $node->setType($type);
        $node->setSequence($sequence);
        $node->setTask($task);
        $node->setResource($resourceConfig);

        $task->addNode($node);

        return $node;
    }

    private function createTag(string $name): Tag
    {
        $catalogType = new CatalogType();
        $catalogType->setName('test-type');
        $catalogType->setCode('test_type');
        self::getEntityManager()->persist($catalogType);

        $catalog = new Catalog();
        $catalog->setName('Test Category');
        $catalog->setType($catalogType);
        $catalog->setEnabled(true);
        self::getEntityManager()->persist($catalog);

        $tag = new Tag();
        $tag->setName($name);
        $tag->setType(TagType::StaticTag);
        $tag->setCatalog($catalog);
        $tag->setValid(true);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        return $tag;
    }
}
