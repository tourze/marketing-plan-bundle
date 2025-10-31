<?php

declare(strict_types=1);

namespace MarketingPlanBundle\Service;

use Knp\Menu\ItemInterface;
use MarketingPlanBundle\Entity\Log;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Entity\UserProgress;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 营销计划管理主菜单
        $marketingPlan = $item->addChild('营销计划管理', ['icon' => 'fas fa-rocket']);

        // 核心功能
        $marketingPlan->addChild('自动化流程', ['icon' => 'fas fa-tasks'])
            ->setUri($this->linkGenerator->getCurdListPage(Task::class))
        ;

        $marketingPlan->addChild('流程节点', ['icon' => 'fas fa-sitemap'])
            ->setUri($this->linkGenerator->getCurdListPage(Node::class))
        ;

        // 执行状态
        $marketingPlan->addChild('用户进度', ['icon' => 'fas fa-user-chart'])
            ->setUri($this->linkGenerator->getCurdListPage(UserProgress::class))
        ;

        $marketingPlan->addChild('节点执行状态', ['icon' => 'fas fa-chart-line'])
            ->setUri($this->linkGenerator->getCurdListPage(NodeStage::class))
        ;

        // 配置管理
        $marketingPlan->addChild('节点条件', ['icon' => 'fas fa-filter'])
            ->setUri($this->linkGenerator->getCurdListPage(NodeCondition::class))
        ;

        $marketingPlan->addChild('节点延时', ['icon' => 'fas fa-clock'])
            ->setUri($this->linkGenerator->getCurdListPage(NodeDelay::class))
        ;

        // 日志记录
        $marketingPlan->addChild('执行日志', ['icon' => 'fas fa-list-alt'])
            ->setUri($this->linkGenerator->getCurdListPage(Log::class))
        ;
    }
}
