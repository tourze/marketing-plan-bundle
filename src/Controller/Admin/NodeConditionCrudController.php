<?php

namespace MarketingPlanBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Enum\ConditionOperator;

/**
 * @extends AbstractCrudController<NodeCondition>
 */
#[AdminCrud(
    routePath: '/marketing-plan/node-condition',
    routeName: 'marketing_plan_node_condition'
)]
final class NodeConditionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NodeCondition::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('节点条件')
            ->setEntityLabelInPlural('节点条件')
            ->setPageTitle('index', '节点条件管理')
            ->setPageTitle('new', '创建节点条件')
            ->setPageTitle('edit', '编辑节点条件')
            ->setPageTitle('detail', '节点条件详情')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', '名称');
        yield TextField::new('field', '字段');
        yield ChoiceField::new('operator', '操作符')
            ->setChoices(ConditionOperator::cases())
            ->setFormTypeOptions([
                'class' => ConditionOperator::class,
                'choice_label' => fn (ConditionOperator $operator) => $operator->getLabel(),
            ])
        ;
        yield TextareaField::new('value', '值')
            ->setHelp('支持文本或JSON格式的数据')
            ->setNumOfRows(3)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '名称'))
            ->add(TextFilter::new('field', '字段'))
            ->add(ChoiceFilter::new('operator', '操作符')
                ->setChoices(array_combine(
                    array_map(fn (ConditionOperator $op) => $op->getLabel(), ConditionOperator::cases()),
                    ConditionOperator::cases()
                ))
            )
            ->add(TextFilter::new('value', '值'))
        ;
    }
}
