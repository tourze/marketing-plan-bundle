<?php

namespace MarketingPlanBundle\Command;

use MarketingPlanBundle\Repository\TaskRepository;
use MarketingPlanBundle\Service\TaskService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'marketing-plan:check-task-status',
    description: '检查任务状态，自动开始和结束任务',
)]
class CheckTaskStatusCommand extends Command
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
        private readonly TaskService $taskService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tasks = $this->taskRepository->findAll();

        foreach ($tasks as $task) {
            $this->taskService->checkStatus($task);
        }

        return Command::SUCCESS;
    }
}
