<?php

namespace MarketingPlanBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\ProgressStatus;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;

class NodeFixtures extends Fixture implements DependentFixtureInterface
{
    public const NODE_REFERENCE = 'node';

    public function load(ObjectManager $manager): void
    {
        /** @var Task $task */
        $task = $this->getReference(TaskFixtures::TASK_REFERENCE, Task::class);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setTask($task);
        $node->setName('开始节点');
        $node->setType(NodeType::START);
        $node->setActionClass('MarketingPlanBundle\Action\StartAction');
        $node->setOrder(1);
        $node->setIsActive(true);
        $node->setIsSkippable(false);
        $node->setStatus(ProgressStatus::PENDING->value);
        $node->setResource($resourceConfig);

        $manager->persist($node);
        $manager->flush();

        $this->addReference(self::NODE_REFERENCE, $node);
    }

    public function getDependencies(): array
    {
        return [
            TaskFixtures::class,
        ];
    }
}
