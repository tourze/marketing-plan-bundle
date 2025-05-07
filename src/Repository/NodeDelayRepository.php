<?php

namespace MarketingPlanBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Enum\DelayType;

/**
 * @method NodeDelay|null find($id, $lockMode = null, $lockVersion = null)
 * @method NodeDelay|null findOneBy(array $criteria, array $orderBy = null)
 * @method NodeDelay[]    findAll()
 * @method NodeDelay[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeDelayRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NodeDelay::class);
    }

    public function findByTypeAndValue(DelayType $type, int $value): array
    {
        return $this->createQueryBuilder('nd')
            ->andWhere('nd.type = :type')
            ->andWhere('nd.value = :value')
            ->setParameter('type', $type)
            ->setParameter('value', $value)
            ->getQuery()
            ->getResult();
    }

    public function findPendingDelays(): array
    {
        return $this->createQueryBuilder('nd')
            ->andWhere('nd.type = :specificTime')
            ->andWhere('nd.specificTime > :now')
            ->setParameter('specificTime', DelayType::SPECIFIC_TIME)
            ->setParameter('now', new \DateTime())
            ->orderBy('nd.specificTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
