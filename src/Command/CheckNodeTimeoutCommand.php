<?php

namespace MarketingPlanBundle\Command;

use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Exception\TaskException;
use MarketingPlanBundle\Repository\NodeRepository;
use MarketingPlanBundle\Service\UserProgressService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: self::NAME,
    description: '检查节点超时状态，标记流失用户',
)]
class CheckNodeTimeoutCommand extends Command
{
    public const NAME = 'marketing-plan:check-node-timeout';

    public function __construct(
        private readonly NodeRepository $nodeRepository,
        private readonly UserProgressService $userProgressService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $nodes = $this->nodeRepository->findAll();

        foreach ($nodes as $node) {
            if (null === $node->getDelay()) {
                continue;
            }

            $delay = $node->getDelay();

            // 处理具体时间的情况
            if (DelayType::SPECIFIC_TIME === $delay->getType()) {
                $specificTime = $delay->getSpecificTime();
                if (null === $specificTime) {
                    throw new TaskException('Specific time is required for SPECIFIC_TIME delay type');
                }
                $beforeTime = $specificTime;
            } else {
                // 处理其他类型的延时
                $beforeTime = match ($delay->getType()) {
                    DelayType::MINUTES => $now->modify('-' . $delay->getValue() . ' minutes'),
                    DelayType::HOURS => $now->modify('-' . $delay->getValue() . ' hours'),
                    DelayType::DAYS => $now->modify('-' . $delay->getValue() . ' days'),
                };
            }

            $this->userProgressService->checkTimeoutDropped($node, $beforeTime);
        }

        return Command::SUCCESS;
    }
}
