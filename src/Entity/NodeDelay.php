<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Repository\NodeDelayRepository;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: NodeDelayRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_delay', options: ['comment' => '节点延时配置'])]
class NodeDelay implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\Column(length: 50, enumType: DelayType::class, options: ['comment' => '延时类型'])]
    private DelayType $type = DelayType::MINUTES;

    #[ORM\Column(options: ['comment' => '延时值'])]
    private int $value = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '具体时间'])]
    private ?\DateTimeImmutable $specificTime = null;

    #[ORM\OneToOne(inversedBy: 'delay')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Node $node = null;

    use TimestampableAware;

    public function __construct()
    {
        $this->type = DelayType::MINUTES;
        $this->value = 0;
    }

    public function __toString(): string
    {
        if ($this->type === DelayType::SPECIFIC_TIME && null !== $this->specificTime) {
            return "延时至 {$this->specificTime->format('Y-m-d H:i:s')}";
        }
        return "{$this->value} {$this->type->getLabel()}";
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

    public function getSpecificTime(): ?\DateTimeImmutable
    {
        return $this->specificTime;
    }

    public function setSpecificTime(?\DateTimeImmutable $specificTime): static
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
