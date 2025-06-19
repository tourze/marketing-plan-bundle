<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\ConditionOperator;
use MarketingPlanBundle\Repository\NodeConditionRepository;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: NodeConditionRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_condition', options: ['comment' => '节点条件'])]
class NodeCondition implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(length: 255, options: ['comment' => '条件名称'])]
    private string $name;

    #[ORM\Column(length: 255, options: ['comment' => '字段'])]
    private string $field;

    #[ORM\Column(length: 50, enumType: ConditionOperator::class, options: ['comment' => '操作符'])]
    private ConditionOperator $operator;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '值'])]
    private string $value;

    #[ORM\ManyToOne(inversedBy: 'conditions')]
    #[ORM\JoinColumn(nullable: false)]
    private Node $node;

    use TimestampableAware;

    public function __toString(): string
    {
        return "{$this->name} ({$this->field} {$this->operator->value} {$this->value})";
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
