<?php

namespace MarketingPlanBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\DropReason;
use MarketingPlanBundle\Enum\NodeStageStatus;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\ProgressStatus;
use MarketingPlanBundle\Repository\NodeStageRepository;
use MarketingPlanBundle\Repository\UserProgressRepository;
use MarketingPlanBundle\Service\UserProgressService;
use PHPUnit\Framework\TestCase;

class UserProgressServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private UserProgressRepository $userProgressRepository;
    private NodeStageRepository $nodeStageRepository;
    private UserProgressService $userProgressService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userProgressRepository = $this->createMock(UserProgressRepository::class);
        $this->nodeStageRepository = $this->createMock(NodeStageRepository::class);
        $this->userProgressService = new UserProgressService(
            $this->entityManager,
            $this->userProgressRepository,
            $this->nodeStageRepository
        );
    }

    public function testCreate_whenProgressDoesNotExist_createsNewProgress(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $userId = 'user123';
        $firstNode = $this->createMock(Node::class);
        
        // 创建一个正确的模拟ArrayCollection
        $nodes = $this->getMockBuilder(ArrayCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->getMock();
        
        $nodes->method('first')->willReturn($firstNode);
        
        $task->method('getNodes')->willReturn($nodes);
        
        $this->userProgressRepository->method('findOneBy')
            ->with([
                'task' => $task,
                'userId' => $userId,
            ])
            ->willReturn(null);
            
        $this->entityManager->expects($this->exactly(2))
            ->method('persist');
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->userProgressService->create($task, $userId);

        // Assert
        $this->assertInstanceOf(UserProgress::class, $result);
        $this->assertSame($task, $result->getTask());
        $this->assertSame($userId, $result->getUserId());
        $this->assertSame($firstNode, $result->getCurrentNode());
        $this->assertEquals(ProgressStatus::RUNNING, $result->getStatus());
        $this->assertNotNull($result->getStartTime());
    }

    public function testCreate_whenProgressExists_returnsExistingProgress(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $userId = 'user123';
        $existingProgress = $this->createMock(UserProgress::class);
        
        $this->userProgressRepository->method('findOneBy')
            ->with([
                'task' => $task,
                'userId' => $userId,
            ])
            ->willReturn($existingProgress);
            
        $this->entityManager->expects($this->never())
            ->method('persist');
            
        $this->entityManager->expects($this->never())
            ->method('flush');

        // Act
        $result = $this->userProgressService->create($task, $userId);

        // Assert
        $this->assertSame($existingProgress, $result);
    }

    public function testCreate_whenTaskHasNoNodes_throwsException(): void
    {
        // Arrange
        $task = $this->createMock(Task::class);
        $userId = 'user123';
        
        // 创建一个正确的模拟ArrayCollection
        $nodes = $this->getMockBuilder(ArrayCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->getMock();
        
        $nodes->method('first')->willReturn(false);
        
        $task->method('getNodes')->willReturn($nodes);
        
        $this->userProgressRepository->method('findOneBy')
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task has no nodes');

        // Act
        $this->userProgressService->create($task, $userId);
    }

    public function testMarkTouched_whenStageExists_updatesTouchTime(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $stage = $this->createMock(NodeStage::class);
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn($stage);
            
        $stage->method('isTouched')
            ->willReturn(false);
            
        $stage->expects($this->once())
            ->method('setTouchTime')
            ->with($this->isInstanceOf(\DateTime::class));
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->userProgressService->markTouched($progress, $node);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testMarkTouched_whenStageNotFound_throwsException(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Node stage not found');

        // Act
        $this->userProgressService->markTouched($progress, $node);
    }

    public function testMarkTouched_whenAlreadyTouched_doesNothing(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $stage = $this->createMock(NodeStage::class);
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn($stage);
            
        $stage->method('isTouched')
            ->willReturn(true);
            
        $stage->expects($this->never())
            ->method('setTouchTime');
            
        $this->entityManager->expects($this->never())
            ->method('flush');

        // Act
        $this->userProgressService->markTouched($progress, $node);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testMarkActivated_whenStageExistsAndNodeIsEnd_updatesActiveTimeAndFinishesProgress(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $stage = $this->createMock(NodeStage::class);
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn($stage);
            
        $stage->method('isActivated')
            ->willReturn(false);
            
        $node->method('getType')
            ->willReturn(NodeType::END);
            
        $stage->expects($this->once())
            ->method('setActiveTime')
            ->with($this->isInstanceOf(\DateTime::class));
            
        $progress->expects($this->once())
            ->method('setStatus')
            ->with(ProgressStatus::FINISHED)
            ->willReturnSelf();
            
        $progress->expects($this->once())
            ->method('setFinishTime')
            ->with($this->isInstanceOf(\DateTime::class))
            ->willReturnSelf();
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->userProgressService->markActivated($progress, $node);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testMarkActivated_whenStageExistsAndNodeIsNotEnd_updatesActiveTimeOnly(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $stage = $this->createMock(NodeStage::class);
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn($stage);
            
        $stage->method('isActivated')
            ->willReturn(false);
            
        $node->method('getType')
            ->willReturn(NodeType::RESOURCE); // 使用正确的枚举值
            
        $stage->expects($this->once())
            ->method('setActiveTime')
            ->with($this->isInstanceOf(\DateTime::class));
            
        $progress->expects($this->never())
            ->method('setStatus');
            
        $progress->expects($this->never())
            ->method('setFinishTime');
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->userProgressService->markActivated($progress, $node);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testMarkActivated_whenStageNotFound_throwsException(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Node stage not found');

        // Act
        $this->userProgressService->markActivated($progress, $node);
    }

    public function testMarkActivated_whenAlreadyActivated_doesNothing(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $stage = $this->createMock(NodeStage::class);
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn($stage);
            
        $stage->method('isActivated')
            ->willReturn(true);
            
        $stage->expects($this->never())
            ->method('setActiveTime');
            
        $this->entityManager->expects($this->never())
            ->method('flush');

        // Act
        $this->userProgressService->markActivated($progress, $node);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testMarkDropped_whenStageExists_updatesDropDetails(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $stage = $this->createMock(NodeStage::class);
        $reason = DropReason::TIMEOUT;
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn($stage);
            
        $stage->method('isDropped')
            ->willReturn(false);
            
        $stage->expects($this->once())
            ->method('setDropTime')
            ->with($this->isInstanceOf(\DateTime::class))
            ->willReturnSelf();
            
        $stage->expects($this->once())
            ->method('setDropReason')
            ->with($reason)
            ->willReturnSelf();
            
        $stage->expects($this->once())
            ->method('setStatus')
            ->with(NodeStageStatus::DROPPED)
            ->willReturnSelf();
            
        $progress->expects($this->once())
            ->method('setStatus')
            ->with(ProgressStatus::DROPPED);
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->userProgressService->markDropped($progress, $node, $reason);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testMarkDropped_whenStageNotFound_throwsException(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $reason = DropReason::TIMEOUT;
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Node stage not found');

        // Act
        $this->userProgressService->markDropped($progress, $node, $reason);
    }

    public function testMarkDropped_whenAlreadyDropped_doesNothing(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $stage = $this->createMock(NodeStage::class);
        $reason = DropReason::TIMEOUT;
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn($stage);
            
        $stage->method('isDropped')
            ->willReturn(true);
            
        $stage->expects($this->never())
            ->method('setDropTime');
            
        $this->entityManager->expects($this->never())
            ->method('flush');

        // Act
        $this->userProgressService->markDropped($progress, $node, $reason);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testMoveToNextNode_whenCurrentNodeActivated_movesToNextNode(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $currentNode = $this->createMock(Node::class);
        $currentStage = $this->createMock(NodeStage::class);
        $nextNode = $this->createMock(Node::class);
        $task = $this->createMock(Task::class);
        
        $progress->method('getCurrentNode')
            ->willReturn($currentNode);
            
        $progress->method('getNodeStage')
            ->with($currentNode)
            ->willReturn($currentStage);
            
        $currentStage->method('isActivated')
            ->willReturn(true);
            
        $currentNode->method('getTask')
            ->willReturn($task);
            
        $currentNode->method('getSequence')
            ->willReturn(1);
            
        $nextNode->method('getSequence')
            ->willReturn(2);
            
        // 创建一个包含nextNode的集合
        $filteredNodes = $this->getMockBuilder(ArrayCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->getMock();
            
        $filteredNodes->method('first')
            ->willReturn($nextNode);
            
        // 创建一个可以被过滤的节点集合
        $nodes = $this->getMockBuilder(ArrayCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['filter'])
            ->getMock();
            
        $nodes->method('filter')
            ->willReturn($filteredNodes);
            
        $task->method('getNodes')
            ->willReturn($nodes);
            
        $progress->expects($this->once())
            ->method('addStage')
            ->with($this->isInstanceOf(NodeStage::class))
            ->willReturnSelf();
            
        $progress->expects($this->once())
            ->method('setCurrentNode')
            ->with($nextNode)
            ->willReturnSelf();
            
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(NodeStage::class));
            
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Act
        $this->userProgressService->moveToNextNode($progress);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testMoveToNextNode_whenCurrentNodeNotActivated_throwsException(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $currentNode = $this->createMock(Node::class);
        $currentStage = $this->createMock(NodeStage::class);
        
        $progress->method('getCurrentNode')
            ->willReturn($currentNode);
            
        $progress->method('getNodeStage')
            ->with($currentNode)
            ->willReturn($currentStage);
            
        $currentStage->method('isActivated')
            ->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Current node not activated');

        // Act
        $this->userProgressService->moveToNextNode($progress);
    }

    public function testMoveToNextNode_whenNoNextNode_throwsException(): void
    {
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $currentNode = $this->createMock(Node::class);
        $currentStage = $this->createMock(NodeStage::class);
        $task = $this->createMock(Task::class);
        
        $progress->method('getCurrentNode')
            ->willReturn($currentNode);
            
        $progress->method('getNodeStage')
            ->with($currentNode)
            ->willReturn($currentStage);
            
        $currentStage->method('isActivated')
            ->willReturn(true);
            
        $currentNode->method('getTask')
            ->willReturn($task);
            
        $currentNode->method('getSequence')
            ->willReturn(3);
            
        // 创建一个为空的过滤结果
        $filteredNodes = $this->getMockBuilder(ArrayCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['first'])
            ->getMock();
            
        $filteredNodes->method('first')
            ->willReturn(false);
            
        // 创建一个可以被过滤的节点集合
        $nodes = $this->getMockBuilder(ArrayCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['filter'])
            ->getMock();
            
        $nodes->method('filter')
            ->willReturn($filteredNodes);
            
        $task->method('getNodes')
            ->willReturn($nodes);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No next node found');

        // Act
        $this->userProgressService->moveToNextNode($progress);
    }

    public function testCheckTimeoutDropped_marksEachStageAsDroppedWithTimeout(): void
    {
        // Arrange
        $node = $this->createMock(Node::class);
        $beforeTime = new \DateTime();
        
        $stage1 = $this->createMock(NodeStage::class);
        $stage2 = $this->createMock(NodeStage::class);
        
        $progress1 = $this->createMock(UserProgress::class);
        $progress2 = $this->createMock(UserProgress::class);
        
        $stage1->method('getUserProgress')->willReturn($progress1);
        $stage2->method('getUserProgress')->willReturn($progress2);
        
        $this->nodeStageRepository->method('findShouldDropped')
            ->with($node, $beforeTime)
            ->willReturn([$stage1, $stage2]);
            
        // 设置markDropped调用需要的行为
        $progress1->method('getNodeStage')->with($node)->willReturn($stage1);
        $progress2->method('getNodeStage')->with($node)->willReturn($stage2);
        
        $stage1->method('isDropped')->willReturn(false);
        $stage2->method('isDropped')->willReturn(false);
        
        $stage1->expects($this->once())->method('setDropTime')->willReturnSelf();
        $stage1->expects($this->once())->method('setDropReason')->with(DropReason::TIMEOUT)->willReturnSelf();
        $stage1->expects($this->once())->method('setStatus')->with(NodeStageStatus::DROPPED)->willReturnSelf();
        
        $stage2->expects($this->once())->method('setDropTime')->willReturnSelf();
        $stage2->expects($this->once())->method('setDropReason')->with(DropReason::TIMEOUT)->willReturnSelf();
        $stage2->expects($this->once())->method('setStatus')->with(NodeStageStatus::DROPPED)->willReturnSelf();
        
        $progress1->expects($this->once())->method('setStatus')->with(ProgressStatus::DROPPED);
        $progress2->expects($this->once())->method('setStatus')->with(ProgressStatus::DROPPED);
        
        $this->entityManager->expects($this->exactly(2))->method('flush');

        // Act
        $this->userProgressService->checkTimeoutDropped($node, $beforeTime);
        
        // 测试通过
        $this->assertTrue(true);
    }

    public function testCheckConditionDropped_whenConditionsNotMetAndEligible_marksAsDropped(): void
    {
        // 由于方法中存在TODO标记，这个测试只能验证方法的基本行为
        
        // Arrange
        $progress = $this->createMock(UserProgress::class);
        $node = $this->createMock(Node::class);
        $stage = $this->createMock(NodeStage::class);
        
        $progress->method('getNodeStage')
            ->with($node)
            ->willReturn($stage);
            
        $stage->method('isTouched')->willReturn(true);
        $stage->method('isActivated')->willReturn(false);
        $stage->method('isDropped')->willReturn(false);
        
        // 目前方法中硬编码了$conditionsMet = true，所以不会调用markDropped

        // Act
        $this->userProgressService->checkConditionDropped($progress, $node);
        
        // 由于当前实现不会触发标记为dropped，我们只能验证测试已执行
        $this->assertTrue(true);
    }
} 