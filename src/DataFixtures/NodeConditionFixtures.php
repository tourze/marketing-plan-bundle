<?php

namespace MarketingPlanBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeCondition;
use MarketingPlanBundle\Enum\ConditionOperator;

class NodeConditionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Node $node */
        $node = $this->getReference(NodeFixtures::NODE_REFERENCE, Node::class);

        $condition1 = new NodeCondition();
        $condition1->setNode($node);
        $condition1->setName('用户等级检查');
        $condition1->setField('user_level');
        $condition1->setOperator(ConditionOperator::GREATER_THAN_OR_EQUAL);
        $condition1->setValue('3');

        $manager->persist($condition1);

        $condition2 = new NodeCondition();
        $condition2->setNode($node);
        $condition2->setName('订单金额检查');
        $condition2->setField('order_amount');
        $condition2->setOperator(ConditionOperator::GREATER_THAN);
        $condition2->setValue('1000');

        $manager->persist($condition2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            NodeFixtures::class,
        ];
    }
}
