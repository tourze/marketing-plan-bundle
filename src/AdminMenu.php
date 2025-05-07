<?php

namespace MarketingPlanBundle;

use Knp\Menu\ItemInterface;
use MarketingPlanBundle\Entity\Task;
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

#[MenuProvider]
class AdminMenu
{
    public function __construct(private readonly LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        $item->addChild('自动化流程')->setUri($this->linkGenerator->getCurdListPage(Task::class));
    }
}
