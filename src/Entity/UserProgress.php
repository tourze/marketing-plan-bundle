<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\ProgressStatus;
use MarketingPlanBundle\Repository\UserProgressRepository;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

#[ORM\Entity(repositoryClass: UserProgressRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_user_progress', options: ['comment' => '用户流程进度'])]
#[ORM\UniqueConstraint(columns: ['task_id', 'user_id'])]
#[ORM\Index(columns: ['current_node_id', 'status'], name: 'idx_current_node_status')]
class UserProgress implements \Stringable
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
    #[ORM\JoinColumn(nullable: false)]
    private Task $task;

    #[ORM\Column(length: 50, options: ['comment' => '用户ID'])]
    private string $userId;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'current_node_id', nullable: false)]
    private Node $currentNode;

    #[ORM\Column(length: 50, enumType: ProgressStatus::class, options: ['comment' => '当前状态'])]
    private ProgressStatus $status = ProgressStatus::PENDING;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始时间'])]
    private \DateTimeImmutable $startTime;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '完成时间'])]
    private ?\DateTimeImmutable $finishTime = null;

    /**
     * @var Collection<int, NodeStage>
     */
    #[ORM\OneToMany(mappedBy: 'userProgress', targetEntity: NodeStage::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(value: ['reachTime' => 'ASC'])]
    private Collection $stages;

    use TimestampableAware;

    public function __construct()
    {
        $this->stages = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "UserProgress #{$this->id} - User: {$this->userId} - Status: {$this->status->value}";
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

    public function getCurrentNode(): Node
    {
        return $this->currentNode;
    }

    public function setCurrentNode(Node $currentNode): static
    {
        $this->currentNode = $currentNode;

        return $this;
    }

    public function getStatus(): ProgressStatus
    {
        return $this->status;
    }

    public function setStatus(ProgressStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

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

    /**
     * @return Collection<int, NodeStage>
     */
    public function getStages(): Collection
    {
        return $this->stages;
    }

    public function addStage(NodeStage $stage): static
    {
        if (!$this->stages->contains($stage)) {
            $this->stages->add($stage);
            $stage->setUserProgress($this);
        }

        return $this;
    }

    public function removeStage(NodeStage $stage): static
    {
        if ($this->stages->removeElement($stage)) {
            // NodeStage's userProgress property is not nullable, so we can't set it to null
            // The stage will be orphaned and removed by Doctrine's orphanRemoval
        }

        return $this;
    }

    public function getNodeStage(Node $node): ?NodeStage
    {
        foreach ($this->stages as $stage) {
            if ($stage->getNode() === $node) {
                return $stage;
            }
        }

        return null;
    }

    public function isNodeTouched(Node $node): bool
    {
        $stage = $this->getNodeStage($node);

        return null !== $stage && $stage->isTouched();
    }

    public function isNodeActivated(Node $node): bool
    {
        $stage = $this->getNodeStage($node);

        return null !== $stage && $stage->isActivated();
    }

    public function isNodeDropped(Node $node): bool
    {
        $stage = $this->getNodeStage($node);

        return null !== $stage && $stage->isDropped();
    }
}
