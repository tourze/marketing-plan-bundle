<?php

declare(strict_types=1);

namespace MarketingPlanBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use MarketingPlanBundle\Entity\NodeStage;
use MarketingPlanBundle\Enum\DropReason;
use MarketingPlanBundle\Enum\NodeStageStatus;

/**
 * @extends AbstractCrudController<NodeStage>
 */
#[AdminCrud(
    routePath: '/marketing-plan/node-stage',
    routeName: 'marketing_plan_node_stage'
)]
final class NodeStageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return NodeStage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('节点执行状态')
            ->setEntityLabelInPlural('节点执行状态列表')
            ->setPageTitle(Crud::PAGE_INDEX, '节点执行状态列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建节点执行状态')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑节点执行状态')
            ->setPageTitle(Crud::PAGE_DETAIL, '节点执行状态详情')
            ->setDefaultSort(['reachTime' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('关联信息');
        yield AssociationField::new('userProgress', '用户进度')
            ->setCrudController(UserProgressCrudController::class)
            ->autocomplete()
        ;
        yield AssociationField::new('node', '节点')
            ->setCrudController(NodeCrudController::class)
            ->autocomplete()
        ;

        yield FormField::addFieldset('状态信息');
        yield ChoiceField::new('status', '状态')
            ->setChoices(NodeStageStatus::cases())
            ->setFormTypeOptions([
                'class' => NodeStageStatus::class,
                'choice_label' => fn (NodeStageStatus $status) => $status->getLabel(),
            ])
            ->renderAsBadges([
                NodeStageStatus::PENDING->value => 'secondary',
                NodeStageStatus::RUNNING->value => 'primary',
                NodeStageStatus::FINISHED->value => 'success',
                NodeStageStatus::DROPPED->value => 'danger',
            ])
        ;
        yield ChoiceField::new('dropReason', '流失原因')
            ->setChoices(DropReason::cases())
            ->setFormTypeOptions([
                'class' => DropReason::class,
                'choice_label' => fn (DropReason $reason) => $reason->getLabel(),
                'required' => false,
            ])
            ->hideOnIndex()
        ;

        yield FormField::addFieldset('时间信息');
        yield DateTimeField::new('reachTime', '进入节点时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
        yield DateTimeField::new('touchTime', '触达时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
        yield DateTimeField::new('activeTime', '激活时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
        yield DateTimeField::new('finishTime', '完成时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
        yield DateTimeField::new('dropTime', '流失时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;

        yield FormField::addFieldset('系统信息');
        yield DateTimeField::new('createTime', '创建时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
        yield DateTimeField::new('updateTime', '更新时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('userProgress', '用户进度'))
            ->add(EntityFilter::new('node', '节点'))
            ->add(ChoiceFilter::new('status', '状态')
                ->setChoices(array_combine(
                    array_map(fn (NodeStageStatus $status) => $status->getLabel(), NodeStageStatus::cases()),
                    NodeStageStatus::cases()
                ))
            )
            ->add(ChoiceFilter::new('dropReason', '流失原因')
                ->setChoices(array_combine(
                    array_map(fn (DropReason $reason) => $reason->getLabel(), DropReason::cases()),
                    DropReason::cases()
                ))
            )
            ->add(DateTimeFilter::new('reachTime', '进入节点时间'))
            ->add(DateTimeFilter::new('touchTime', '触达时间'))
            ->add(DateTimeFilter::new('finishTime', '完成时间'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::EDIT)
        ;
    }
}
