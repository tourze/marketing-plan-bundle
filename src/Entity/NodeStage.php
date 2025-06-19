<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\DropReason;
use MarketingPlanBundle\Enum\NodeStageStatus;
use MarketingPlanBundle\Repository\NodeStageRepository;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: NodeStageRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_stage', options: ['comment' => '节点执行状态'])]
#[ORM\Index(columns: ['user_progress_id', 'node_id'], name: 'idx_progress_node')]
#[ORM\Index(columns: ['status'], name: 'idx_status')]
class NodeStage implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_progress_id', nullable: false)]
    private UserProgress $userProgress;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'node_id', nullable: false)]
    private Node $node;

    #[ORM\Column(length: 50, enumType: NodeStageStatus::class, options: ['comment' => '状态'])]
    private NodeStageStatus $status = NodeStageStatus::PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '进入节点时间'])]
    private \DateTimeImmutable $reachTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '触达时间'])]
    private ?\DateTimeImmutable $touchTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '激活时间'])]
    private ?\DateTimeImmutable $activeTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeImmutable $finishTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '流失时间'])]
    private ?\DateTimeImmutable $dropTime = null;

    #[ORM\Column(length: 50, enumType: DropReason::class, nullable: true, options: ['comment' => '流失原因'])]
    private ?DropReason $dropReason = null;

    use TimestampableAware;

    public function __toString(): string
    {
        return "NodeStage #{$this->id} - {$this->status->value}";
    }

    public function getUserProgress(): UserProgress
    {
        return $this->userProgress;
    }

    public function setUserProgress(UserProgress $userProgress): static
    {
        $this->userProgress = $userProgress;

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

    public function getStatus(): NodeStageStatus
    {
        return $this->status;
    }

    public function setStatus(NodeStageStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getReachTime(): \DateTimeImmutable
    {
        return $this->reachTime;
    }

    public function setReachTime(\DateTimeImmutable $reachTime): static
    {
        $this->reachTime = $reachTime;

        return $this;
    }

    public function getTouchTime(): ?\DateTimeImmutable
    {
        return $this->touchTime;
    }

    public function setTouchTime(?\DateTimeImmutable $touchTime): static
    {
        $this->touchTime = $touchTime;

        return $this;
    }

    public function getActiveTime(): ?\DateTimeImmutable
    {
        return $this->activeTime;
    }

    public function setActiveTime(?\DateTimeImmutable $activeTime): static
    {
        $this->activeTime = $activeTime;

        return $this;
    }

    public function getFinishTime(): ?\DateTimeImmutable
    {
        return $this->finishTime;
    }

    public function setFinishTime(?\DateTimeImmutable $finishTime): static
    {
        $this->finishTime = $finishTime;

        return $this;
    }

    public function getDropTime(): ?\DateTimeImmutable
    {
        return $this->dropTime;
    }

    public function setDropTime(?\DateTimeImmutable $dropTime): static
    {
        $this->dropTime = $dropTime;

        return $this;
    }

    public function getDropReason(): ?DropReason
    {
        return $this->dropReason;
    }

    public function setDropReason(?DropReason $dropReason): static
    {
        $this->dropReason = $dropReason;

        return $this;
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
