<?php

namespace MarketingPlanBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\NodeStageStatus;
use MarketingPlanBundle\Enum\NodeType;
use MarketingPlanBundle\Enum\ProgressStatus;
use MarketingPlanBundle\Enum\TaskStatus;
use Tourze\ResourceManageBundle\Entity\ResourceConfig;
use UserTagBundle\Entity\Tag;

class NodeStageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $crowd = new Tag();
        $crowd->setName('test-tag-node-stage');
        $manager->persist($crowd);

        $task = new Task();
        $task->setTitle('Test Task for NodeStage');
        $task->setDescription('Test Description');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setCrowd($crowd);
        $task->setStartTime(new \DateTimeImmutable());
        $task->setEndTime(new \DateTimeImmutable('+1 day'));
        $manager->persist($task);

        $resourceConfig = new ResourceConfig();
        $resourceConfig->setType('none');
        $resourceConfig->setAmount(0);

        $node = new Node();
        $node->setName('Test Node for NodeStage');
        $node->setType(NodeType::START);
        $node->setSequence(1);
        $node->setTask($task);
        $node->setResource($resourceConfig);
        $manager->persist($node);

        $userProgress = new UserProgress();
        $userProgress->setTask($task);
        $userProgress->setUserId('test-user-123');
        $userProgress->setCurrentNode($node);
        $userProgress->setStatus(ProgressStatus::RUNNING);
        $userProgress->setStartTime(new \DateTimeImmutable());
        $manager->persist($userProgress);

        $nodeStage = new NodeStage();
        $nodeStage->setUserProgress($userProgress);
        $nodeStage->setNode($node);
        $nodeStage->setStatus(NodeStageStatus::RUNNING);
        $nodeStage->setReachTime(new \DateTimeImmutable());
        $manager->persist($nodeStage);

        $manager->flush();
    }
}
