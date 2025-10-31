<?php

namespace MarketingPlanBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MarketingPlanBundle\Entity\Log;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\LogStatus;

class LogFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Task $task */
        $task = $this->getReference(TaskFixtures::TASK_REFERENCE, Task::class);

        // 创建多个 Log 实例
        for ($i = 1; $i <= 5; ++$i) {
            $log = new Log();
            $log->setTask($task);
            $log->setUserId('user_' . $i);
            $log->setStatus(LogStatus::cases()[array_rand(LogStatus::cases())]);
            $log->setContext(['test' => true, 'iteration' => $i]);
            $log->setProgressData(['percent' => $i * 20]);

            $manager->persist($log);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TaskFixtures::class,
        ];
    }
}
