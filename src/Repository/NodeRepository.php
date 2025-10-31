<?php

namespace MarketingPlanBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Enum\NodeType;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Node>
 */
#[AsRepository(entityClass: Node::class)]
class NodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Node::class);
    }

    /**
     * @return array<Node>
     */
    public function findByTaskAndType(string $taskId, NodeType $type): array
    {
        /** @var array<Node> */
        return $this->createQueryBuilder('n')
            ->andWhere('n.task = :taskId')
            ->andWhere('n.type = :type')
            ->setParameter('taskId', $taskId)
            ->setParameter('type', $type)
            ->orderBy('n.sequence', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<Node>
     */
    public function findNextNodes(string $nodeId): array
    {
        /** @var array<Node> */
        return $this->createQueryBuilder('n')
            ->innerJoin(Node::class, 'currentNode', 'WITH', 'currentNode.id = :nodeId')
            ->andWhere('n.sequence > currentNode.sequence')
            ->andWhere('n.task = currentNode.task')
            ->setParameter('nodeId', $nodeId)
            ->orderBy('n.sequence', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Node $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Node $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param mixed $delay
     * @return array<Node>
     */
    public function findByDelay(mixed $delay): array
    {
        if (null === $delay) {
            /** @var array<Node> */
            return $this->createQueryBuilder('n')
                ->leftJoin('n.delay', 'd')
                ->andWhere('d.id IS NULL')
                ->getQuery()
                ->getResult()
            ;
        }

        /** @var array<Node> */
        return $this->createQueryBuilder('n')
            ->innerJoin('n.delay', 'd')
            ->andWhere('d = :delay')
            ->setParameter('delay', $delay)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countByDelay(mixed $delay): int
    {
        if (null === $delay) {
            return (int) $this->createQueryBuilder('n')
                ->select('COUNT(n.id)')
                ->leftJoin('n.delay', 'd')
                ->andWhere('d.id IS NULL')
                ->getQuery()
                ->getSingleScalarResult()
            ;
        }

        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->innerJoin('n.delay', 'd')
            ->andWhere('d = :delay')
            ->setParameter('delay', $delay)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * 重写默认的 findOneBy 方法以正确处理 delay 字段
     */
    public function findOneBy(array $criteria, ?array $orderBy = null): ?Node
    {
        $hasDelayInCriteria = array_key_exists('delay', $criteria);
        $hasDelayInOrderBy = null !== $orderBy && array_key_exists('delay', $orderBy);

        // 如果涉及 delay 字段，特殊处理
        if ($hasDelayInCriteria || $hasDelayInOrderBy) {
            return $this->handleDelayQuery($criteria, $orderBy);
        }

        return parent::findOneBy($criteria, $orderBy);
    }

    /**
     * 处理包含 delay 字段的查询
     *
     * @param array<string, mixed> $criteria
     * @param array<string, string>|null $orderBy
     */
    private function handleDelayQuery(array $criteria, ?array $orderBy): ?Node
    {
        $qb = $this->createQueryBuilder('n');

        $this->addDelayJoinAndCriteria($qb, $criteria);
        $this->addRemainingCriteria($qb, $criteria);
        $this->addOrderByClause($qb, $orderBy);

        /** @var Node|null */
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array<string, mixed> $criteria
     */
    private function addDelayJoinAndCriteria(QueryBuilder $qb, array $criteria): void
    {
        if (array_key_exists('delay', $criteria)) {
            $delay = $criteria['delay'];
            // 不能修改传入的参数，所以直接处理

            if (null === $delay) {
                $qb->leftJoin('n.delay', 'd')->andWhere('d.id IS NULL');
            } else {
                $qb->innerJoin('n.delay', 'd')->andWhere('d = :delay')->setParameter('delay', $delay);
            }
        } else {
            $qb->leftJoin('n.delay', 'd');
        }
    }

    /**
     * @param array<string, mixed> $criteria
     */
    private function addRemainingCriteria(QueryBuilder $qb, array $criteria): void
    {
        $filteredCriteria = array_filter(
            $criteria,
            fn (string $field): bool => 'delay' !== $field,
            ARRAY_FILTER_USE_KEY
        );

        foreach ($filteredCriteria as $field => $value) {
            $qb->andWhere("n.{$field} = :{$field}")->setParameter($field, $value);
        }
    }

    /**
     * @param array<string, string>|null $orderBy
     */
    private function addOrderByClause(QueryBuilder $qb, ?array $orderBy): void
    {
        if (null === $orderBy) {
            return;
        }

        foreach ($orderBy as $field => $direction) {
            if ('delay' === $field) {
                $qb->addOrderBy('d.id', $direction);
            } else {
                $qb->addOrderBy("n.{$field}", $direction);
            }
        }
    }
}
