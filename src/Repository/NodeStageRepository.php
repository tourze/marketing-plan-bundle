<?php

namespace MarketingPlanBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Enum\NodeStageStatus;

/**
 * @method NodeStage|null find($id, $lockMode = null, $lockVersion = null)
 * @method NodeStage|null findOneBy(array $criteria, array $orderBy = null)
 * @method NodeStage[]    findAll()
 * @method NodeStage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeStageRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NodeStage::class);
    }

    /**
     * 统计某个节点的用户状态
     *
     * @return array{
     *     total: int,          // 总进入人数
     *     touched: int,        // 已触达人数
     *     activated: int,      // 已激活人数
     *     dropped: int         // 已流失人数
     * }
     */
    public function countNodeStats(Node $node): array
    {
        $qb = $this->createQueryBuilder('ns');

        return $qb->select(
            'COUNT(ns.id) as total',
            'COUNT(ns.touchTime) as touched',
            'COUNT(ns.activeTime) as activated',
            'COUNT(ns.dropTime) as dropped'
        )
            ->andWhere('ns.node = :node')
            ->setParameter('node', $node)
            ->getQuery()
            ->getSingleResult();
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
        return $this->createQueryBuilder('ns')
            ->andWhere('ns.node = :node')
            ->andWhere('ns.activeTime IS NOT NULL')
            ->andWhere('ns.dropTime IS NULL')
            ->andWhere('ns.status = :status')
            ->setParameter('node', $currentNode)
            ->setParameter('status', NodeStageStatus::RUNNING)
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
        return $this->createQueryBuilder('ns')
            ->andWhere('ns.node = :node')
            ->andWhere('ns.touchTime IS NOT NULL')
            ->andWhere('ns.activeTime IS NULL')
            ->andWhere('ns.dropTime IS NULL')
            ->andWhere('ns.touchTime < :beforeTime')
            ->setParameter('node', $node)
            ->setParameter('beforeTime', $beforeTime)
            ->getQuery()
            ->getResult();
    }
}
