<?php

namespace MarketingPlanBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Enum\DelayType;

class NodeDelayCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NodeDelay::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('type', '类型')
            ->setChoices(DelayType::cases())
            ->setFormTypeOption('choice_label', fn (DelayType $type) => $type->getLabel());
        yield TextField::new('value', '值');
        yield DateTimeField::new('specificTime', '具体时间')
            ->setRequired(false);
    }
}
