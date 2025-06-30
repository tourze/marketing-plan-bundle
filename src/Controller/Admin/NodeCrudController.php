<?php

namespace MarketingPlanBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Enum\DelayType;
use MarketingPlanBundle\Enum\NodeType;
use Tourze\ResourceManageBundle\Service\ResourceManager;

class NodeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ResourceManager $resourceManager,
    ) {
    }

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
            ->setPageTitle(Crud::PAGE_DETAIL, '节点详情');
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
            ]);
        yield IntegerField::new('sequence', '序号');

        yield FormField::addFieldset('资源配置');
        yield ChoiceField::new('resource.type', '资源类型')
            ->setChoices(array_combine(
                array_column($this->resourceManager->genSelectData(), 'label'),
                array_column($this->resourceManager->genSelectData(), 'value'),
            ));
        yield TextField::new('resource.typeId', '资源ID');
        yield IntegerField::new('resource.amount', '派发数量');
        yield IntegerField::new('resource.expireDay', '有效天数');
        yield DateTimeField::new('resource.expireTime', '到期时间');

        yield FormField::addFieldset('条件配置');
        yield CollectionField::new('conditions', '条件')
            ->useEntryCrudForm(NodeConditionCrudController::class)
            ->setEntryIsComplex(true)
            ->allowAdd()
            ->allowDelete();

        yield FormField::addFieldset('延时配置');
        yield ChoiceField::new('delay.type', '延时类型')
            ->setChoices(DelayType::cases())
            ->setFormTypeOptions([
                'class' => DelayType::class,
                'choice_label' => fn (DelayType $type) => $type->getLabel(),
            ]);
        yield IntegerField::new('delay.value', '延时值');
        yield DateTimeField::new('delay.specificTime', '具体时间');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('创建节点');
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
}
