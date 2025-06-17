<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\LogStatus;
use MarketingPlanBundle\Repository\LogRepository;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '阶段记录')]
#[Deletable]
#[Creatable]
#[Editable]
#[ORM\Entity(repositoryClass: LogRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_log', options: ['comment' => '阶段记录'])]
#[ORM\Index(columns: ['task_id', 'user_id', 'create_time'], name: 'ims_marketing_plan_log_idx_task_user_time')]
class Log
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Task $task;

    #[ORM\Column(length: 255, options: ['comment' => '用户ID'])]
    private string $userId;

    #[ORM\Column(length: 50, enumType: LogStatus::class, options: ['comment' => '状态'])]
    private LogStatus $status = LogStatus::IN_PROGRESS;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文数据'])]
    private ?array $context = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeInterface $completeTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '失败原因'])]
    private ?string $failureReason = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '进度数据'])]
    private ?array $progressData = null;

    use TimestampableAware;

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

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getStatus(): LogStatus
    {
        return $this->status;
    }

    public function setStatus(LogStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getCompleteTime(): ?\DateTimeInterface
    {
        return $this->completeTime;
    }

    public function setCompleteTime(?\DateTimeInterface $completeTime): static
    {
        $this->completeTime = $completeTime;

        return $this;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): static
    {
        $this->failureReason = $failureReason;

        return $this;
    }

    public function getProgressData(): ?array
    {
        return $this->progressData;
    }

    public function setProgressData(?array $progressData): static
    {
        $this->progressData = $progressData;

        return $this;
    }
}
