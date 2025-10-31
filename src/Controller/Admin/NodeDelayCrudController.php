<?php

namespace MarketingPlanBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Enum\DelayType;

/**
 * @extends AbstractCrudController<NodeDelay>
 */
#[AdminCrud(
    routePath: '/marketing-plan/node-delay',
    routeName: 'marketing_plan_node_delay'
)]
final class NodeDelayCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NodeDelay::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('节点延时')
            ->setEntityLabelInPlural('节点延时')
            ->setPageTitle('index', '节点延时管理')
            ->setPageTitle('new', '创建节点延时')
            ->setPageTitle('edit', '编辑节点延时')
            ->setPageTitle('detail', '节点延时详情')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield ChoiceField::new('type', '类型')
            ->setChoices(DelayType::cases())
            ->setFormTypeOption('choice_label', fn (DelayType $type) => $type->getLabel())
        ;
        yield IntegerField::new('value', '值');
        yield DateTimeField::new('specificTime', '具体时间')
            ->setRequired(false)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('type', '类型')
                ->setChoices(array_combine(
                    array_map(fn (DelayType $type) => $type->getLabel(), DelayType::cases()),
                    DelayType::cases()
                ))
            )
            ->add(TextFilter::new('value', '值'))
            ->add(DateTimeFilter::new('specificTime', '具体时间'))
        ;
    }
}
