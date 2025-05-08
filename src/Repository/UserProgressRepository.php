<?php

namespace MarketingPlanBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\ProgressStatus;

/**
 * @method UserProgress|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserProgress|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserProgress[]    findAll()
 * @method UserProgress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserProgressRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserProgress::class);
    }

    /**
     * 查找某个任务的所有进行中的用户进度
     */
    public function findRunningByTask(Task $task): array
    {
        return $this->createQueryBuilder('up')
            ->andWhere('up.task = :task')
            ->andWhere('up.status = :status')
            ->setParameter('task', $task)
            ->setParameter('status', ProgressStatus::RUNNING)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找某个节点当前的用户进度
     */
    public function findByCurrentNode(Node $node): array
    {
        return $this->createQueryBuilder('up')
            ->andWhere('up.currentNode = :node')
            ->setParameter('node', $node)
            ->getQuery()
            ->getResult();
    }

    /**
     * 统计某个任务的各个状态的用户数量
     *
     * @return array{pending: int, running: int, finished: int, dropped: int}
     */
    public function countByTaskStatus(Task $task): array
    {
        $result = $this->createQueryBuilder('up')
            ->select('up.status, COUNT(up.id) as count')
            ->andWhere('up.task = :task')
            ->setParameter('task', $task)
            ->groupBy('up.status')
            ->getQuery()
            ->getResult();

        $counts = [
            'pending' => 0,
            'running' => 0,
            'finished' => 0,
            'dropped' => 0,
        ];

        foreach ($result as $row) {
            $counts[$row['status']->value] = (int) $row['count'];
        }

        return $counts;
    }

    /**
     * 查找需要进入下一个节点的用户
     * 条件：
     * 1. 当前节点已激活
     * 2. 未流失
     * 3. 未完成整个流程
     */
    public function findReadyForNextNode(Node $currentNode): array
    {
        return $this->createQueryBuilder('up')
            ->join('up.stages', 'ns')
            ->andWhere('up.currentNode = :node')
            ->andWhere('up.status = :status')
            ->andWhere('ns.node = :node')
            ->andWhere('ns.activeTime IS NOT NULL')
            ->andWhere('ns.dropTime IS NULL')
            ->setParameter('node', $currentNode)
            ->setParameter('status', ProgressStatus::RUNNING)
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找需要标记为流失的用户
     * 条件：
     * 1. 已触达
     * 2. 未激活
     * 3. 超过指定时间
     */
    public function findShouldDropped(Node $node, \DateTimeInterface $beforeTime): array
    {
        return $this->createQueryBuilder('up')
            ->join('up.stages', 'ns')
            ->andWhere('up.currentNode = :node')
            ->andWhere('up.status = :status')
            ->andWhere('ns.node = :node')
            ->andWhere('ns.touchTime IS NOT NULL')
            ->andWhere('ns.activeTime IS NULL')
            ->andWhere('ns.dropTime IS NULL')
            ->andWhere('ns.touchTime < :beforeTime')
            ->setParameter('node', $node)
            ->setParameter('status', ProgressStatus::RUNNING)
            ->setParameter('beforeTime', $beforeTime)
            ->getQuery()
            ->getResult();
    }
}
