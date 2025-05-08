<?php

namespace MarketingPlanBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Enum\NodeType;

/**
 * @method Node|null find($id, $lockMode = null, $lockVersion = null)
 * @method Node|null findOneBy(array $criteria, array $orderBy = null)
 * @method Node[]    findAll()
 * @method Node[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Node::class);
    }

    public function findByTaskAndType(string $taskId, NodeType $type): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.task = :taskId')
            ->andWhere('n.type = :type')
            ->setParameter('taskId', $taskId)
            ->setParameter('type', $type)
            ->orderBy('n.sequence', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findNextNodes(string $nodeId): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.sequence > (SELECT n2.sequence FROM ' . Node::class . ' n2 WHERE n2.nodeId = :nodeId)')
            ->andWhere('n.task = (SELECT n2.task FROM ' . Node::class . ' n2 WHERE n2.nodeId = :nodeId)')
            ->setParameter('nodeId', $nodeId)
            ->orderBy('n.sequence', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
