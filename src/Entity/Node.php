<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Repository\NodeRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

#[ORM\Entity(repositoryClass: NodeRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node', options: ['comment' => '流程节点'])]
class Node implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    use BlameableAware;

    #[ORM\Column(length: 255, options: ['comment' => '节点名称'])]
    private string $name;

    #[ORM\Column(length: 50, enumType: NodeType::class, options: ['comment' => '节点类型'])]
    private NodeType $type;

    #[ORM\Column(options: ['comment' => '序号'])]
    private int $sequence = 1;

    #[ORM\Embedded(class: ResourceConfig::class)]
    private ?ResourceConfig $resource = null;

    #[ORM\OneToMany(mappedBy: 'node', targetEntity: NodeCondition::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $conditions;

    #[ORM\OneToOne(mappedBy: 'node', targetEntity: NodeDelay::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?NodeDelay $delay = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'nodes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Task $task = null;

    use TimestampableAware;

    public function __construct()
    {
        $this->conditions = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->getId() || 0 === $this->getId()) {
            return '';
        }

        return "{$this->getName()} ({$this->getType()->getLabel()})";
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

    public function getType(): NodeType
    {
        return $this->type;
    }

    public function setType(NodeType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getSequence(): int
    {
        return $this->sequence;
    }

    public function setSequence(int $sequence): static
    {
        $this->sequence = $sequence;

        return $this;
    }

    public function getResource(): ?ResourceConfig
    {
        return $this->resource;
    }

    public function setResource(?ResourceConfig $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return Collection<int, NodeCondition>
     */
    public function getConditions(): Collection
    {
        return $this->conditions;
    }

    public function addCondition(NodeCondition $condition): static
    {
        if (!$this->conditions->contains($condition)) {
            $this->conditions->add($condition);
            $condition->setNode($this);
        }

        return $this;
    }

    public function removeCondition(NodeCondition $condition): static
    {
        if ($this->conditions->removeElement($condition)) {
            // NodeCondition's node property is not nullable, so we can't set it to null
            // The condition will be orphaned and removed by Doctrine's orphanRemoval
        }

        return $this;
    }

    public function getDelay(): ?NodeDelay
    {
        return $this->delay;
    }

    public function setDelay(?NodeDelay $delay): static
    {
        if (null === $delay && null !== $this->delay) {
            $oldDelay = $this->delay;
            $this->delay = null;
            $oldDelay->setNode(null);
        }

        if (null !== $delay && $delay->getNode() !== $this) {
            $this->delay = $delay;
            $delay->setNode($this);
        }

        return $this;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): static
    {
        $this->task = $task;

        return $this;
    }
}
