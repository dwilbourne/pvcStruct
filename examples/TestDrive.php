<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare(strict_types=1);

namespace pvcExamples\struct;

use DI\ContainerBuilder;
use integration_tests\fixture\TreenodeConfigurationsFixture;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\search\SearchStrategyInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\tree\treesearch\SearchStrategyDepthFirst;
use pvc\struct\treesearch\err\StartNodeUnsetException;

/**
 * Class TestDrive
 */
class TestDrive extends TestCase
{
    protected TreeOrderedInterface $tree;

    protected TreenodeConfigurationsFixture $fixture;

    protected SearchStrategyDepthFirst $strategy;

    public function setUp(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions(__DIR__ . '/DiConfig.php');
        $container = $builder->build();
        $this->tree = $container->get(TreeOrderedInterface::class);
        $this->fixture = $container->get('fixture');
        $this->tree->hydrate($this->fixture->makeDtoArray());
        $this->strategy = $container->get(SearchStrategyInterface::class);
    }

    /**
     * testDepthFirstPreorderSearch
     * @throws StartNodeUnsetException
     * @covers \pvc\struct\tree\treesearch\SearchStrategyDepthFirst::getNodes
     */
    public function testDepthFirstPreorderSearch(): void
    {
        $expectedNodeIds = $this->fixture->makeOrderedDepthFirstArrayOfAllNodeIds();
        $this->strategy->setStartNode($this->tree->getRoot());
        $actualNodes = $this->strategy->getNodes();
        $actualNodeIds = $this->fixture->makeArrayOfNodeIdsFromArrayOfNodes($actualNodes);
        self::assertEqualsCanonicalizing($expectedNodeIds, $actualNodeIds);
    }
}
