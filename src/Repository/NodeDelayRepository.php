<?php

namespace MarketingPlanBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Enum\DelayType;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<NodeDelay>
 */
#[AsRepository(entityClass: NodeDelay::class)]
class NodeDelayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NodeDelay::class);
    }

    /**
     * @return array<NodeDelay>
     */
    public function findByTypeAndValue(DelayType $type, int $value): array
    {
        /** @var array<NodeDelay> */
        return $this->createQueryBuilder('nd')
            ->andWhere('nd.type = :type')
            ->andWhere('nd.value = :value')
            ->setParameter('type', $type)
            ->setParameter('value', $value)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<NodeDelay>
     */
    public function findPendingDelays(): array
    {
        /** @var array<NodeDelay> */
        return $this->createQueryBuilder('nd')
            ->andWhere('nd.type = :specificTime')
            ->andWhere('nd.specificTime > :now')
            ->setParameter('specificTime', DelayType::SPECIFIC_TIME)
            ->setParameter('now', new \DateTime())
            ->orderBy('nd.specificTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(NodeDelay $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NodeDelay $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
