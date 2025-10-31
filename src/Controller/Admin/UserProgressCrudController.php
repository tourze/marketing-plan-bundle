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
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use MarketingPlanBundle\Entity\UserProgress;
use MarketingPlanBundle\Enum\ProgressStatus;

/**
 * @extends AbstractCrudController<UserProgress>
 */
#[AdminCrud(
    routePath: '/marketing-plan/user-progress',
    routeName: 'marketing_plan_user_progress'
)]
final class UserProgressCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserProgress::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('用户流程进度')
            ->setEntityLabelInPlural('用户流程进度列表')
            ->setPageTitle(Crud::PAGE_INDEX, '用户流程进度列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建用户流程进度')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑用户流程进度')
            ->setPageTitle(Crud::PAGE_DETAIL, '用户流程进度详情')
            ->setDefaultSort(['startTime' => 'DESC'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addFieldset('基本信息');
        yield AssociationField::new('task', '任务')
            ->setCrudController(TaskCrudController::class)
            ->autocomplete()
        ;
        yield TextField::new('userId', '用户ID');
        yield AssociationField::new('currentNode', '当前节点')
            ->setCrudController(NodeCrudController::class)
            ->autocomplete()
        ;

        yield FormField::addFieldset('状态信息');
        yield ChoiceField::new('status', '状态')
            ->setChoices(ProgressStatus::cases())
            ->setFormTypeOptions([
                'class' => ProgressStatus::class,
                'choice_label' => fn (ProgressStatus $status) => $status->getLabel(),
            ])
            ->renderAsBadges([
                ProgressStatus::PENDING->value => 'secondary',
                ProgressStatus::RUNNING->value => 'primary',
                ProgressStatus::FINISHED->value => 'success',
                ProgressStatus::DROPPED->value => 'danger',
            ])
        ;

        yield FormField::addFieldset('时间信息');
        yield DateTimeField::new('startTime', '开始时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;
        yield DateTimeField::new('finishTime', '完成时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnForm()
        ;

        yield FormField::addFieldset('节点执行状态');
        yield CollectionField::new('stages', '节点执行状态')
            ->setEntryIsComplex(true)
            ->hideOnForm()
            ->onlyOnDetail()
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
            ->add(EntityFilter::new('task', '任务'))
            ->add(TextFilter::new('userId', '用户ID'))
            ->add(EntityFilter::new('currentNode', '当前节点'))
            ->add(ChoiceFilter::new('status', '状态')
                ->setChoices(array_combine(
                    array_map(fn (ProgressStatus $status) => $status->getLabel(), ProgressStatus::cases()),
                    ProgressStatus::cases()
                ))
            )
            ->add(DateTimeFilter::new('startTime', '开始时间'))
            ->add(DateTimeFilter::new('finishTime', '完成时间'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
}
