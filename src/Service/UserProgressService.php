<?php

namespace MarketingPlanBundle\Service;

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

class UserProgressService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserProgressRepository $userProgressRepository,
        private readonly NodeStageRepository $nodeStageRepository,
    ) {
    }

    /**
     * 创建用户进度
     * 1. 检查用户是否已经有进度
     * 2. 创建进度记录
     * 3. 创建第一个节点的状态
     */
    public function create(Task $task, string $userId): UserProgress
    {
        // 检查是否已存在
        $progress = $this->userProgressRepository->findOneBy([
            'task' => $task,
            'userId' => $userId,
        ]);

        if (null !== $progress) {
            return $progress;
        }

        // 获取第一个节点
        $firstNode = $task->getNodes()->first();
        if (false === $firstNode) {
            throw new \RuntimeException('Task has no nodes');
        }

        // 创建进度
        $progress = new UserProgress();
        $progress->setTask($task)
            ->setUserId($userId)
            ->setCurrentNode($firstNode)
            ->setStatus(ProgressStatus::RUNNING)
            ->setStartTime(new \DateTime());

        // 创建第一个节点的状态
        $stage = new NodeStage();
        $stage->setNode($firstNode)
            ->setUserProgress($progress)
            ->setStatus(NodeStageStatus::RUNNING)
            ->setReachTime(new \DateTime());

        $progress->addStage($stage);

        $this->entityManager->persist($progress);
        $this->entityManager->persist($stage);
        $this->entityManager->flush();

        return $progress;
    }

    /**
     * 标记节点已触达
     * 1. 检查节点状态
     * 2. 更新触达时间
     */
    public function markTouched(UserProgress $progress, Node $node): void
    {
        $stage = $progress->getNodeStage($node);
        if (null === $stage) {
            throw new \RuntimeException('Node stage not found');
        }

        if ($stage->isTouched()) {
            return;
        }

        $stage->setTouchTime(new \DateTime());
        $this->entityManager->flush();
    }

    /**
     * 标记节点已激活
     * 1. 检查节点状态
     * 2. 更新激活时间
     * 3. 如果是最后一个节点，标记流程完成
     */
    public function markActivated(UserProgress $progress, Node $node): void
    {
        $stage = $progress->getNodeStage($node);
        if (null === $stage) {
            throw new \RuntimeException('Node stage not found');
        }

        if ($stage->isActivated()) {
            return;
        }

        $stage->setActiveTime(new \DateTime());

        // 如果是最后一个节点，标记流程完成
        if (NodeType::END === $node->getType()) {
            $progress->setStatus(ProgressStatus::FINISHED)
                ->setFinishTime(new \DateTime());
        }

        $this->entityManager->flush();
    }

    /**
     * 标记节点已流失
     * 1. 检查节点状态
     * 2. 更新流失时间和原因
     * 3. 标记流程中途退出
     */
    public function markDropped(UserProgress $progress, Node $node, DropReason $reason): void
    {
        $stage = $progress->getNodeStage($node);
        if (null === $stage) {
            throw new \RuntimeException('Node stage not found');
        }

        if ($stage->isDropped()) {
            return;
        }

        $stage->setDropTime(new \DateTime())
            ->setDropReason($reason)
            ->setStatus(NodeStageStatus::DROPPED);

        $progress->setStatus(ProgressStatus::DROPPED);

        $this->entityManager->flush();
    }

    /**
     * 进入下一个节点
     * 1. 检查当前节点是否已激活
     * 2. 获取下一个节点
     * 3. 创建新节点的状态
     */
    public function moveToNextNode(UserProgress $progress): void
    {
        $currentNode = $progress->getCurrentNode();
        $currentStage = $progress->getNodeStage($currentNode);

        if (null === $currentStage || !$currentStage->isActivated()) {
            throw new \RuntimeException('Current node not activated');
        }

        // 获取下一个节点
        $nextNode = $currentNode->getTask()->getNodes()
            ->filter(fn (Node $node) => $node->getSequence() > $currentNode->getSequence())
            ->first();

        if (false === $nextNode) {
            throw new \RuntimeException('No next node found');
        }

        // 创建新节点的状态
        $stage = new NodeStage();
        $stage->setNode($nextNode)
            ->setUserProgress($progress)
            ->setStatus(NodeStageStatus::RUNNING)
            ->setReachTime(new \DateTime());

        $progress->addStage($stage)
            ->setCurrentNode($nextNode);

        $this->entityManager->persist($stage);
        $this->entityManager->flush();
    }

    /**
     * 检查超时流失
     * 1. 查找需要标记为流失的用户
     * 2. 批量标记为流失
     */
    public function checkTimeoutDropped(Node $node, \DateTimeInterface $beforeTime): void
    {
        $stages = $this->nodeStageRepository->findShouldDropped($node, $beforeTime);

        foreach ($stages as $stage) {
            $this->markDropped($stage->getUserProgress(), $node, DropReason::TIMEOUT);
        }
    }

    /**
     * 检查条件不满足流失
     * 1. 检查节点条件
     * 2. 如果不满足条件，标记为流失
     */
    public function checkConditionDropped(UserProgress $progress, Node $node): void
    {
        $stage = $progress->getNodeStage($node);
        if (null === $stage || !$stage->isTouched() || $stage->isActivated() || $stage->isDropped()) {
            return;
        }

        // TODO: 检查条件是否满足
        // 这里需要实现条件检查的逻辑
        $conditionsMet = true;

        if (!$conditionsMet) {
            $this->markDropped($progress, $node, DropReason::CONDITION_NOT_MET);
        }
    }
}
