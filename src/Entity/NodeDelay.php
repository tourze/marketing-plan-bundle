<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Repository\NodeDelayRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;

#[ORM\Entity(repositoryClass: NodeDelayRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_delay', options: ['comment' => '节点延时配置'])]
class NodeDelay
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
    #[ORM\Column(length: 50, enumType: DelayType::class, options: ['comment' => '延时类型'])]
    private DelayType $type = DelayType::MINUTES;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(options: ['comment' => '延时值'])]
    private int $value = 0;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '具体时间'])]
    private ?\DateTimeInterface $specificTime = null;

    #[ORM\OneToOne(inversedBy: 'delay')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Node $node = null;

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

    public function __construct()
    {
        $this->type = DelayType::MINUTES;
        $this->value = 0;
    }

    public function getType(): DelayType
    {
        return $this->type;
    }

    public function setType(DelayType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getSpecificTime(): ?\DateTimeInterface
    {
        return $this->specificTime;
    }

    public function setSpecificTime(?\DateTimeInterface $specificTime): static
    {
        $this->specificTime = $specificTime;

        return $this;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(?Node $node): static
    {
        // 处理旧的关联
        if (null === $node && null !== $this->node) {
            $oldNode = $this->node;
            $this->node = null;
            if ($oldNode->getDelay() === $this) {
                $oldNode->setDelay(null);
            }
        }

        // 处理新的关联
        if (null !== $node && $node->getDelay() !== $this) {
            $this->node = $node;
            $node->setDelay($this);
        }

        return $this;
    }
}
