<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\ConditionOperator;
use MarketingPlanBundle\Repository\NodeConditionRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: NodeConditionRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_condition', options: ['comment' => '节点条件'])]
class NodeCondition implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\Column(length: 255, options: ['comment' => '条件名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    #[ORM\Column(length: 255, options: ['comment' => '字段'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $field;

    #[ORM\Column(length: 50, enumType: ConditionOperator::class, options: ['comment' => '操作符'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [ConditionOperator::class, 'cases'])]
    private ConditionOperator $operator;

    #[ORM\Column(type: Types::TEXT, options: ['comment' => '值'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 65535)]
    private string $value;

    #[ORM\ManyToOne(inversedBy: 'conditions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private Node $node;

    public function __toString(): string
    {
        return "{$this->name} ({$this->field} {$this->operator->value} {$this->value})";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): void
    {
        $this->field = $field;
    }

    public function getOperator(): ConditionOperator
    {
        return $this->operator;
    }

    public function setOperator(ConditionOperator $operator): void
    {
        $this->operator = $operator;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): void
    {
        $this->node = $node;
    }
}
