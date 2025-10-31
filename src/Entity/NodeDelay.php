<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Repository\NodeDelayRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: NodeDelayRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_delay', options: ['comment' => '节点延时配置'])]
class NodeDelay implements \Stringable
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

    #[ORM\Column(length: 50, enumType: DelayType::class, options: ['comment' => '延时类型'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [DelayType::class, 'cases'])]
    private DelayType $type = DelayType::MINUTES;

    #[ORM\Column(options: ['comment' => '延时值'])]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $value = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '具体时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $specificTime = null;

    #[ORM\OneToOne(targetEntity: Node::class, inversedBy: 'delay')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Node $node = null;

    public function __construct()
    {
        $this->type = DelayType::MINUTES;
        $this->value = 0;
    }

    public function __toString(): string
    {
        if (DelayType::SPECIFIC_TIME === $this->type && null !== $this->specificTime) {
            return "延时至 {$this->specificTime->format('Y-m-d H:i:s')}";
        }

        return "{$this->value} {$this->type->getLabel()}";
    }

    public function getType(): DelayType
    {
        return $this->type;
    }

    public function setType(DelayType $type): void
    {
        $this->type = $type;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getSpecificTime(): ?\DateTimeImmutable
    {
        return $this->specificTime;
    }

    public function setSpecificTime(?\DateTimeImmutable $specificTime): void
    {
        $this->specificTime = $specificTime;
    }

    public function getNode(): ?Node
    {
        return $this->node;
    }

    public function setNode(?Node $node): void
    {
        // 断开旧关联
        if (null !== $this->node && $this->node !== $node) {
            $oldNode = $this->node;
            $this->node = null;
            if ($oldNode->getDelay() === $this) {
                $oldNode->setDelay(null);
            }
        }

        // 设置新关联
        $this->node = $node;
        if (null !== $node && $node->getDelay() !== $this) {
            $node->setDelay($this);
        }
    }

    /**
     * Alias for setType() for backward compatibility
     */
    public function setUnit(DelayType $unit): void
    {
        $this->setType($unit);
    }
}
