<?php

namespace MarketingPlanBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MarketingPlanBundle\Entity\Node;
use MarketingPlanBundle\Entity\NodeDelay;
use MarketingPlanBundle\Enum\DelayType;

class NodeDelayFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Node $node */
        $node = $this->getReference(NodeFixtures::NODE_REFERENCE, Node::class);

        $delay = new NodeDelay();
        $delay->setNode($node);
        $delay->setValue(5);
        $delay->setUnit(DelayType::MINUTES);

        $manager->persist($delay);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            NodeFixtures::class,
        ];
    }
}
