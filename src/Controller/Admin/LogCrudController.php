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
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use MarketingPlanBundle\Entity\Log;
use MarketingPlanBundle\Enum\LogStatus;

/**
 * @extends AbstractCrudController<Log>
 */
#[AdminCrud(
    routePath: '/marketing-plan/log',
    routeName: 'marketing_plan_log'
)]
final class LogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Log::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('阶段记录')
            ->setEntityLabelInPlural('阶段记录列表')
            ->setPageTitle(Crud::PAGE_INDEX, '阶段记录列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建阶段记录')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑阶段记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '阶段记录详情')
            ->setDefaultSort(['createTime' => 'DESC'])
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

        yield FormField::addFieldset('状态信息');
        yield ChoiceField::new('status', '状态')
            ->setChoices(LogStatus::cases())
            ->setFormTypeOptions([
                'class' => LogStatus::class,
                'choice_label' => fn (LogStatus $status) => $status->getLabel(),
            ])
            ->renderAsBadges([
                LogStatus::IN_PROGRESS->value => 'primary',
                LogStatus::COMPLETED->value => 'success',
                LogStatus::FAILED->value => 'danger',
                LogStatus::CANCELLED->value => 'warning',
            ])
        ;

        yield FormField::addFieldset('执行信息');
        yield DateTimeField::new('completeTime', '完成时间')
            ->setFormat('yyyy-MM-dd HH:mm:ss')
            ->hideOnIndex()
        ;
        yield TextareaField::new('failureReason', '失败原因')
            ->setNumOfRows(3)
            ->hideOnIndex()
        ;

        yield FormField::addFieldset('数据信息');
        yield CodeEditorField::new('context', '上下文数据')
            ->setLanguage('javascript')
            ->setNumOfRows(8)
            ->hideOnIndex()
            ->onlyOnDetail()
        ;
        yield CodeEditorField::new('progressData', '进度数据')
            ->setLanguage('javascript')
            ->setNumOfRows(8)
            ->hideOnIndex()
            ->onlyOnDetail()
        ;

        yield FormField::addFieldset('系统信息');
        yield TextField::new('createdBy', '创建者')
            ->hideOnForm()
        ;
        yield TextField::new('updatedBy', '更新者')
            ->hideOnForm()
        ;
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
            ->add(ChoiceFilter::new('status', '状态')
                ->setChoices(array_combine(
                    array_map(fn (LogStatus $status) => $status->getLabel(), LogStatus::cases()),
                    LogStatus::cases()
                ))
            )
            ->add(TextFilter::new('createdBy', '创建者'))
            ->add(DateTimeFilter::new('completeTime', '完成时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
        ;
    }
}
