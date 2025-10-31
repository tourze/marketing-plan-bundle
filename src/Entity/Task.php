<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Repository\TaskRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\UserTagContracts\TagInterface;
use UserTagBundle\Entity\Tag;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_task', options: ['comment' => '自动化流程'])]
class Task implements \Stringable
{
    use SnowflakeKeyAware;
    use BlameableAware;
    use TimestampableAware;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = false;

    #[ORM\Column(length: 255, unique: true, options: ['comment' => '流程名称'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '流程描述'])]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    #[ORM\Column(length: 50, enumType: TaskStatus::class, options: ['comment' => '流程状态'])]
    #[Assert\NotNull]
    #[Assert\Choice(callback: [TaskStatus::class, 'cases'])]
    private TaskStatus $status = TaskStatus::DRAFT;

    #[ORM\ManyToOne(targetEntity: Tag::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private TagInterface $crowd;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '开始时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '结束时间'])]
    #[Assert\Type(type: '\DateTimeImmutable')]
    private ?\DateTimeImmutable $endTime = null;

    /**
     * @var Collection<int, Node>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Node::class, mappedBy: 'task', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(value: ['sequence' => 'ASC'])]
    private Collection $nodes;

    /** @var array<string, mixed>|null */
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '流程数据'])]
    #[Assert\Type(type: 'array')]
    private ?array $flowData = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '全局限制'])]
    #[Assert\Type(type: 'integer')]
    private ?int $globalLimit = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '用户限制'])]
    #[Assert\Type(type: 'integer')]
    private ?int $userLimit = null;

    public function __construct()
    {
        $this->nodes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    public function getCrowd(): TagInterface
    {
        return $this->crowd;
    }

    public function setCrowd(TagInterface $crowd): void
    {
        $this->crowd = $crowd;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return Collection<int, Node>
     */
    public function getNodes(): Collection
    {
        return $this->nodes;
    }

    public function addNode(Node $node): void
    {
        if (!$this->nodes->contains($node)) {
            $this->nodes->add($node);
            $node->setTask($this);
        }
    }

    public function removeNode(Node $node): void
    {
        if ($this->nodes->removeElement($node)) {
            if ($node->getTask() === $this) {
                $node->setTask(null);
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getFlowData(): ?array
    {
        return $this->flowData;
    }

    /**
     * @param array<string, mixed>|null $flowData
     */
    public function setFlowData(?array $flowData): void
    {
        $this->flowData = $flowData;
    }

    public function getGlobalLimit(): ?int
    {
        return $this->globalLimit;
    }

    public function setGlobalLimit(?int $globalLimit): void
    {
        $this->globalLimit = $globalLimit;
    }

    public function getUserLimit(): ?int
    {
        return $this->userLimit;
    }

    public function setUserLimit(?int $userLimit): void
    {
        $this->userLimit = $userLimit;
    }
}
