<?php

namespace MarketingPlanBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Enum\ConditionOperator;

class NodeConditionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NodeCondition::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', '名称');
        yield TextField::new('field', '字段');
        yield ChoiceField::new('operator', '操作符')
            ->setChoices(ConditionOperator::cases())
            ->setFormTypeOption('choice_label', fn (ConditionOperator $operator) => $operator->getLabel());
        yield TextField::new('value', '值');
    }
}
