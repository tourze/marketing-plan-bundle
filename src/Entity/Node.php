<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Repository\NodeRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

#[ORM\Entity(repositoryClass: NodeRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node', options: ['comment' => '流程节点'])]
class Node implements \Stringable
{
    use BlameableAware;
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\Column(length: 255, options: ['comment' => '节点名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    #[ORM\Column(length: 50, enumType: NodeType::class, options: ['comment' => '节点类型'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [NodeType::class, 'cases'])]
    private NodeType $type;

    #[ORM\Column(options: ['comment' => '序号'])]
    #[Assert\NotNull]
    #[Assert\PositiveOrZero]
    private int $sequence = 1;

    #[ORM\Embedded(class: ResourceConfig::class)]
    #[Assert\Valid]
    private ?ResourceConfig $resource = null;

    /** @var Collection<int, NodeCondition> */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'node', targetEntity: NodeCondition::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $conditions;

    #[ORM\OneToOne(mappedBy: 'node', targetEntity: NodeDelay::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?NodeDelay $delay = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'nodes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Task $task = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '动作类'])]
    #[Assert\Length(max: 255)]
    private ?string $actionClass = null;

    #[ORM\Column(name: 'sort_order', type: Types::INTEGER, nullable: true, options: ['comment' => '排序'])]
    #[Assert\Type(type: 'integer')]
    private ?int $order = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否激活', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private bool $isActive = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['comment' => '是否可跳过', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private bool $isSkippable = false;

    #[ORM\Column(length: 50, nullable: true, options: ['comment' => '状态'])]
    #[Assert\Length(max: 50)]
    private ?string $status = null;

    public function __construct()
    {
        $this->conditions = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (0 === $this->getId()) {
            return '';
        }

        return "{$this->getName()} ({$this->getType()->getLabel()})";
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): NodeType
    {
        return $this->type;
    }

    public function setType(NodeType $type): void
    {
        $this->type = $type;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getResource(): ?ResourceConfig
    {
        return $this->resource;
    }

    public function setResource(?ResourceConfig $resource): void
    {
        $this->resource = $resource;
    }

    /**
     * @return Collection<int, NodeCondition>
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(NodeCondition $condition): void
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
            $condition->setNode($this);
        }
    }

    public function removeCondition(NodeCondition $condition): void
    {
        if ($this->conditions->removeElement($condition)) {
            // NodeCondition's node property is not nullable, so we can't set it to null
            // The condition will be orphaned and removed by Doctrine's orphanRemoval
        }
    }

    public function getDelay(): ?NodeDelay
    {
        return $this->delay;
    }

    public function setDelay(?NodeDelay $delay): void
    {
        // 断开旧关联
        if (null !== $this->delay && $this->delay !== $delay) {
            $oldDelay = $this->delay;
            $this->delay = null;
            $oldDelay->setNode(null);
        }

        // 设置新关联
        $this->delay = $delay;
        if (null !== $delay && $delay->getNode() !== $this) {
            $delay->setNode($this);
        }
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): void
    {
        $this->task = $task;
    }

    public function getActionClass(): ?string
    {
        return $this->actionClass;
    }

    public function setActionClass(?string $actionClass): void
    {
        $this->actionClass = $actionClass;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function setOrder(?int $order): void
    {
        $this->order = $order;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function isSkippable(): bool
    {
        return $this->isSkippable;
    }

    public function setIsSkippable(bool $isSkippable): void
    {
        $this->isSkippable = $isSkippable;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }
}
