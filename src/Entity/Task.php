<?php

namespace MarketingPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MarketingPlanBundle\Enum\TaskStatus;
use MarketingPlanBundle\Repository\TaskRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\UserTagContracts\TagInterface;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'ims_marketing_plan_task', options: ['comment' => '自动化流程'])]
class Task implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    use BlameableAware;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[ORM\Column(length: 255, unique: true, options: ['comment' => '流程名称'])]
    private string $title;

    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '流程描述'])]
    private ?string $description = null;

    #[ORM\Column(length: 50, enumType: TaskStatus::class, options: ['comment' => '流程状态'])]
    private TaskStatus $status = TaskStatus::DRAFT;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private TagInterface $crowd;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '开始时间'])]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, options: ['comment' => '结束时间'])]
    private ?\DateTimeImmutable $endTime = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'task', targetEntity: Node::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['sequence' => 'ASC'])]
    private Collection $nodes;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '流程数据'])]
    private ?array $flowData = null;

    use TimestampableAware;

    public function __construct()
    {
        $this->nodes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    public function getId(): ?string
    {
        return $this->id;
    }


    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCrowd(): ?TagInterface
    {
        return $this->crowd;
    }

    public function setCrowd(?TagInterface $crowd): static
    {
        $this->crowd = $crowd;

        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeImmutable $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * @return Collection<int, Node>
     */
    public function getNodes(): Collection
    {
        return $this->nodes;
    }

    public function addNode(Node $node): static
    {
        if (!$this->nodes->contains($node)) {
            $this->nodes->add($node);
            $node->setTask($this);
        }

        return $this;
    }

    public function removeNode(Node $node): static
    {
        if ($this->nodes->removeElement($node)) {
            if ($node->getTask() === $this) {
                $node->setTask(null);
            }
        }

        return $this;
    }

    public function getFlowData(): ?array
    {
        return $this->flowData;
    }

    public function setFlowData(?array $flowData): static
    {
        $this->flowData = $flowData;

        return $this;
    }
}
