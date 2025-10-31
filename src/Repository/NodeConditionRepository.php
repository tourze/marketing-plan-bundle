<?php

namespace MarketingPlanBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MarketingPlanBundle\Entity\NodeCondition;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<NodeCondition>
 */
#[AsRepository(entityClass: NodeCondition::class)]
class NodeConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NodeCondition::class);
    }

    /**
     * @return array<NodeCondition>
     */
    public function findByNodeAndField(string $nodeId, string $field): array
    {
        /** @var array<NodeCondition> */
        return $this->createQueryBuilder('nc')
            ->andWhere('nc.node = :nodeId')
            ->andWhere('nc.field = :field')
            ->setParameter('nodeId', $nodeId)
            ->setParameter('field', $field)
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(NodeCondition $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NodeCondition $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
