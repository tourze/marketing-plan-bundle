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
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

#[AsPermission(title: '流程节点')]
#[Deletable]
#[Creatable]
#[Editable]
#[ORM\Entity(repositoryClass: NodeRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node', options: ['comment' => '流程节点'])]
class Node implements \Stringable
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

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 255, options: ['comment' => '节点名称'])]
    private string $name;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 50, enumType: NodeType::class, options: ['comment' => '节点类型'])]
    private NodeType $type;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(options: ['comment' => '序号'])]
    private int $sequence = 1;

    #[ORM\Embedded(class: ResourceConfig::class)]
    #[FormField(title: '资源配置')]
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
        if (!$this->getId()) {
            return '';
        }

        return "{$this->getName()} ({$this->getType()->getLabel()})";
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
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
            if ($condition->getNode() === $this) {
                $condition->setNode(null);
            }
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
