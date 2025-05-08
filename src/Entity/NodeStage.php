<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\DropReason;
use MarketingPlanBundle\Enum\NodeStageStatus;
use MarketingPlanBundle\Repository\NodeStageRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineTimestampBundle\Attribute\UpdateTimeColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;

#[ORM\Entity(repositoryClass: NodeStageRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_node_stage', options: ['comment' => '节点执行状态'])]
#[ORM\Index(columns: ['user_progress_id', 'node_id'], name: 'idx_progress_node')]
#[ORM\Index(columns: ['status'], name: 'idx_status')]
class NodeStage
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'user_progress_id', nullable: false)]
    private UserProgress $userProgress;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'node_id', nullable: false)]
    private Node $node;

    #[ORM\Column(length: 50, enumType: NodeStageStatus::class, options: ['comment' => '状态'])]
    private NodeStageStatus $status = NodeStageStatus::PENDING;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['comment' => '进入节点时间'])]
    private \DateTimeInterface $reachTime;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '触达时间'])]
    private ?\DateTimeInterface $touchTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '激活时间'])]
    private ?\DateTimeInterface $activeTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeInterface $finishTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '流失时间'])]
    private ?\DateTimeInterface $dropTime = null;

    #[ORM\Column(length: 50, enumType: DropReason::class, nullable: true, options: ['comment' => '流失原因'])]
    private ?DropReason $dropReason = null;

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

    public function getReachTime(): \DateTimeInterface
    {
        return $this->reachTime;
    }

    public function setReachTime(\DateTimeInterface $reachTime): static
    {
        $this->reachTime = $reachTime;

        return $this;
    }

    public function getTouchTime(): ?\DateTimeInterface
    {
        return $this->touchTime;
    }

    public function setTouchTime(?\DateTimeInterface $touchTime): static
    {
        $this->touchTime = $touchTime;

        return $this;
    }

    public function getActiveTime(): ?\DateTimeInterface
    {
        return $this->activeTime;
    }

    public function setActiveTime(?\DateTimeInterface $activeTime): static
    {
        $this->activeTime = $activeTime;

        return $this;
    }

    public function getFinishTime(): ?\DateTimeInterface
    {
        return $this->finishTime;
    }

    public function setFinishTime(?\DateTimeInterface $finishTime): static
    {
        $this->finishTime = $finishTime;

        return $this;
    }

    public function getDropTime(): ?\DateTimeInterface
    {
        return $this->dropTime;
    }

    public function setDropTime(?\DateTimeInterface $dropTime): static
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
