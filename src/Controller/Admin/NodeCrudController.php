<?php

namespace MarketingPlanBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Enum\NodeType;

/**
 * @extends AbstractCrudController<Node>
 */
#[AdminCrud(
    routePath: '/marketing-plan/node',
    routeName: 'marketing_plan_node'
)]
final class NodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Node::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('节点')
            ->setEntityLabelInPlural('节点列表')
            ->setPageTitle(Crud::PAGE_INDEX, '节点列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建节点')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑节点')
            ->setPageTitle(Crud::PAGE_DETAIL, '节点详情')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('基本信息');
        yield TextField::new('name', '节点名称');
        yield ChoiceField::new('type', '节点类型')
            ->setChoices(NodeType::cases())
            ->setFormTypeOptions([
                'class' => NodeType::class,
                'choice_label' => fn (NodeType $type) => $type->getLabel(),
            ])
        ;
        yield IntegerField::new('sequence', '序号');

        yield FormField::addFieldset('高级配置');
        yield TextField::new('actionClass', '动作类')
            ->setRequired(false)
            ->setHelp('可选的动作处理类')
        ;
        yield IntegerField::new('order', '排序')
            ->setRequired(false)
            ->setHelp('节点显示顺序')
        ;
        yield ChoiceField::new('isActive', '是否激活')
            ->setChoices(['激活' => true, '禁用' => false])
            ->renderAsBadges([
                true => 'success',
                false => 'secondary',
            ])
        ;
        yield ChoiceField::new('isSkippable', '是否可跳过')
            ->setChoices(['可跳过' => true, '不可跳过' => false])
            ->renderAsBadges([
                true => 'info',
                false => 'warning',
            ])
        ;
        yield TextField::new('status', '状态')
            ->setRequired(false)
        ;

        yield FormField::addFieldset('条件配置');
        yield CollectionField::new('conditions', '条件')
            ->useEntryCrudForm(NodeConditionCrudController::class)
            ->setEntryIsComplex(true)
            ->allowAdd()
            ->allowDelete()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', '节点名称'))
            ->add(ChoiceFilter::new('type', '节点类型')
                ->setChoices(array_combine(
                    array_map(fn (NodeType $type) => $type->getLabel(), NodeType::cases()),
                    NodeType::cases()
                ))
            )
            ->add(NumericFilter::new('sequence', '序号'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('查看');
            })
        ;
    }
}
