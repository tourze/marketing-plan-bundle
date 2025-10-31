<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\DropReason;
use MarketingPlanBundle\Enum\NodeStageStatus;
use MarketingPlanBundle\Repository\NodeStageRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: NodeStageRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_stage', options: ['comment' => '节点执行状态'])]
#[ORM\Index(name: 'ims_marketing_plan_node_stage_idx_progress_node', columns: ['user_progress_id', 'node_id'])]
class NodeStage implements \Stringable
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_progress_id', nullable: false)]
    #[Assert\NotNull]
    private UserProgress $userProgress;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'node_id', nullable: false)]
    #[Assert\NotNull]
    private Node $node;

    #[ORM\Column(length: 50, enumType: NodeStageStatus::class, options: ['comment' => '状态'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [NodeStageStatus::class, 'cases'])]
    #[IndexColumn]
    private NodeStageStatus $status = NodeStageStatus::PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '进入节点时间'])]
    #[Assert\NotNull]
    private \DateTimeImmutable $reachTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '触达时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $touchTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '激活时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $activeTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $finishTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '流失时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $dropTime = null;

    #[ORM\Column(length: 50, enumType: DropReason::class, nullable: true, options: ['comment' => '流失原因'])]
    #[Assert\Choice(callback: [DropReason::class, 'cases'])]
    private ?DropReason $dropReason = null;

    public function __toString(): string
    {
        return "NodeStage #{$this->id} - {$this->status->value}";
    }

    public function getUserProgress(): UserProgress
    {
        return $this->userProgress;
    }

    public function setUserProgress(UserProgress $userProgress): void
    {
        $this->userProgress = $userProgress;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): void
    {
        $this->node = $node;
    }

    public function getStatus(): NodeStageStatus
    {
        return $this->status;
    }

    public function setStatus(NodeStageStatus $status): void
    {
        $this->status = $status;
    }

    public function getReachTime(): \DateTimeImmutable
    {
        return $this->reachTime;
    }

    public function setReachTime(\DateTimeImmutable $reachTime): void
    {
        $this->reachTime = $reachTime;
    }

    public function getTouchTime(): ?\DateTimeImmutable
    {
        return $this->touchTime;
    }

    public function setTouchTime(?\DateTimeImmutable $touchTime): void
    {
        $this->touchTime = $touchTime;
    }

    public function getActiveTime(): ?\DateTimeImmutable
    {
        return $this->activeTime;
    }

    public function setActiveTime(?\DateTimeImmutable $activeTime): void
    {
        $this->activeTime = $activeTime;
    }

    public function getFinishTime(): ?\DateTimeImmutable
    {
        return $this->finishTime;
    }

    public function setFinishTime(?\DateTimeImmutable $finishTime): void
    {
        $this->finishTime = $finishTime;
    }

    public function getDropTime(): ?\DateTimeImmutable
    {
        return $this->dropTime;
    }

    public function setDropTime(?\DateTimeImmutable $dropTime): void
    {
        $this->dropTime = $dropTime;
    }

    public function getDropReason(): ?DropReason
    {
        return $this->dropReason;
    }

    public function setDropReason(?DropReason $dropReason): void
    {
        $this->dropReason = $dropReason;
    }

    public function isTouched(): bool
    {
        return null !== $this->touchTime;
    }

    public function isActivated(): bool
    {
        return null !== $this->activeTime;
    }

    public function isDropped(): bool
    {
        return null !== $this->dropTime;
    }
}
