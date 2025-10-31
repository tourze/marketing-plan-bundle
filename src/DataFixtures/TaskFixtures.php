<?php

namespace MarketingPlanBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\TaskStatus;
use UserTagBundle\Entity\Tag;

class TaskFixtures extends Fixture
{
    public const TASK_REFERENCE = 'task';

    public function load(ObjectManager $manager): void
    {
        // 创建一个测试标签
        $tag = new Tag();
        $tag->setName('测试人群');
        $manager->persist($tag);

        $task = new Task();
        $task->setTitle('测试营销任务');
        $task->setDescription('这是一个测试任务描述');
        $task->setStatus(TaskStatus::DRAFT);
        $task->setCrowd($tag);
        $task->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $task->setEndTime(new \DateTimeImmutable('2024-12-31'));
        $task->setGlobalLimit(100);
        $task->setUserLimit(10);

        $manager->persist($task);
        $manager->flush();

        $this->addReference(self::TASK_REFERENCE, $task);
    }
}
