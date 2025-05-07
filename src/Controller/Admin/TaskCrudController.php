<?php

namespace MarketingPlanBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use MarketingPlanBundle\Entity\Task;
use MarketingPlanBundle\Enum\TaskStatus;

class TaskCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Task::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('任务')
            ->setEntityLabelInPlural('任务列表')
            ->setPageTitle(Crud::PAGE_INDEX, '任务列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建任务')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑任务')
            ->setPageTitle(Crud::PAGE_DETAIL, '任务详情')
            ->setFormThemes([
                '@EasyAdmin/crud/form_theme.html.twig',
                '@MarketingPlan/admin/form_theme.html.twig',
            ]);
    }

    public function configureFields(string $pageName): iterable
    {
        // 基本信息
        yield TextField::new('title', '标题');
        yield TextareaField::new('description', '描述');
        yield AssociationField::new('crowd', '人群');
        yield ChoiceField::new('status', '状态')
            ->setChoices(TaskStatus::cases())
            ->setFormTypeOption('choice_label', fn (TaskStatus $status) => $status->getLabel());
        yield DateTimeField::new('startTime', '开始时间');
        yield DateTimeField::new('endTime', '结束时间');

        // 节点配置（仅在表单和详情页显示）
        if (in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT, Crud::PAGE_DETAIL])) {
            yield CollectionField::new('nodes', '节点配置')
                ->useEntryCrudForm(NodeCrudController::class)
                ->setFormTypeOption('by_reference', false)
                ->setEntryIsComplex(true)
                ->renderExpanded()
                ->addCssClass('field-nodes');
        }

        // 时间信息（仅在列表和详情页显示）
        if (in_array($pageName, [Crud::PAGE_INDEX, Crud::PAGE_DETAIL])) {
            yield DateTimeField::new('createdAt', '创建时间')
                ->hideOnForm();
            yield DateTimeField::new('updatedAt', '更新时间')
                ->hideOnForm();
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('创建任务');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('编辑');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('查看');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('删除');
            });
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('bundles/marketingplan/css/task-form.css')
            ->addJsFile('bundles/marketingplan/js/task-form.js');
    }
}
