<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\LogStatus;
use MarketingPlanBundle\Repository\LogRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: LogRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_log', options: ['comment' => '阶段记录'])]
#[ORM\Index(columns: ['task_id', 'user_id', 'create_time'], name: 'ims_marketing_plan_log_idx_task_user_time')]
class Log implements \Stringable
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

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Task $task;

    #[ORM\Column(length: 255, options: ['comment' => '用户ID'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $userId;

    #[ORM\Column(length: 50, enumType: LogStatus::class, options: ['comment' => '状态'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [LogStatus::class, 'cases'])]
    private LogStatus $status = LogStatus::IN_PROGRESS;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '上下文数据'])]
    #[Assert\Type(type: 'array')]
    private ?array $context = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    #[Assert\Type(type: \DateTimeImmutable::class)]
    private ?\DateTimeImmutable $completeTime = null;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '失败原因'])]
    #[Assert\Length(max: 65535)]
    private ?string $failureReason = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '进度数据'])]
    #[Assert\Type(type: 'array')]
    private ?array $progressData = null;

    public function __toString(): string
    {
        return "Log #{$this->id} - {$this->status->value}";
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): void
    {
        $this->task = $task;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function getStatus(): LogStatus
    {
        return $this->status;
    }

    public function setStatus(LogStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getContext(): ?array
    {
        return $this->context;
    }

    /**
     * @param array<string, mixed>|null $context
     */
    public function setContext(?array $context): void
    {
        $this->context = $context;
    }

    public function getCompleteTime(): ?\DateTimeImmutable
    {
        return $this->completeTime;
    }

    public function setCompleteTime(?\DateTimeImmutable $completeTime): void
    {
        $this->completeTime = $completeTime;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function setFailureReason(?string $failureReason): void
    {
        $this->failureReason = $failureReason;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getProgressData(): ?array
    {
        return $this->progressData;
    }

    /**
     * @param array<string, mixed>|null $progressData
     */
    public function setProgressData(?array $progressData): void
    {
        $this->progressData = $progressData;
    }
}
