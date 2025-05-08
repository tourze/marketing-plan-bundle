<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\ConditionOperator;
use MarketingPlanBundle\Repository\NodeConditionRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;

#[ORM\Entity(repositoryClass: NodeConditionRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_condition', options: ['comment' => '节点条件'])]
class NodeCondition
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 255, options: ['comment' => '条件名称'])]
    private string $name;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 255, options: ['comment' => '字段'])]
    private string $field;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 50, enumType: ConditionOperator::class, options: ['comment' => '操作符'])]
    private ConditionOperator $operator;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(type: Types::TEXT, options: ['comment' => '值'])]
    private string $value;

    #[ORM\ManyToOne(inversedBy: 'conditions')]
    #[ORM\JoinColumn(nullable: false)]
    private Node $node;

    #[Filterable]
    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    #[UpdateTimeColumn]
    #[ListColumn(order: 99, sorter: true)]
    #[Filterable]
    #[ExportColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '更新时间'])]
    private ?\DateTimeInterface $updateTime = null;

    public function setCreateTime(?\DateTimeInterface $createdAt): void
    {
        $this->createTime = $createdAt;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function setUpdateTime(?\DateTimeInterface $updateTime): void
    {
        $this->updateTime = $updateTime;
    }

    public function getUpdateTime(): ?\DateTimeInterface
    {
        return $this->updateTime;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getOperator(): ConditionOperator
    {
        return $this->operator;
    }

    public function setOperator(ConditionOperator $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): static
    {
        $this->node = $node;

        return $this;
    }
}
