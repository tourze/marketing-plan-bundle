<?php

namespace MarketingPlanBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use MarketingPlanBundle\Entity\NodeCondition;

/**
 * @method NodeCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method NodeCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method NodeCondition[]    findAll()
 * @method NodeCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeConditionRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NodeCondition::class);
    }

    public function findByNodeAndField(string $nodeId, string $field): array
    {
        return $this->createQueryBuilder('nc')
            ->andWhere('nc.node = :nodeId')
            ->andWhere('nc.field = :field')
            ->setParameter('nodeId', $nodeId)
            ->setParameter('field', $field)
            ->getQuery()
            ->getResult();
    }
}
