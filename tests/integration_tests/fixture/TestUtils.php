<?php

namespace pvcTests\struct\integration_tests\fixture;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\ReflectionContainer;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\struct\tree\di\TreeDefinitions;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\tree\Tree;
use ReflectionException;

class TestUtils
{
    protected Container $container;

    protected TreenodeConfigurationsFixture $fixture;

    protected int $treeId;

    public function __construct(TreenodeConfigurationsFixture $fixture)
    {
        $aggregate = new DefinitionAggregate(
            TreeDefinitions::makeDefinitions()
        );
        $this->container = new Container($aggregate);
        /**
         * enable autowiring, which recursively evaluates arguments inside the definitions
         */
        $this->container->delegate(new ReflectionContainer());

        $this->fixture = $fixture;
    }

    /**
     * getNodeIdsFromNodeArray
     *
     * @param  array<TreenodeInterface>  $nodeArray
     *
     * @return array<int>
     */
    public static function getNodeIdsFromNodeArray(array $nodeArray): array
    {
        $callback = function (TreenodeInterface $node) {
            return $node->getNodeId();
        };
        /**
         * array_map will preserve keys so just return the array values
         */
        return array_values(array_map($callback, $nodeArray));
    }

    public function testTreeSetup(int $treeId): Tree
    {
        $this->treeId = $treeId;
        $tree = $this->container->get(Tree::class);
        $tree->initialize($this->treeId);
        return $tree;
    }

    /**
     * @return array<TreenodeDto>
     * @throws ReflectionException
     */
    public function makeDtoArray(): array
    {
        $nodeData = $this->fixture->getNodeData();

        /**
         * @param  array<non-negative-int>  $row
         * @return TreenodeDto
         */
        $callback = function (array $row): TreenodeDto {
            /** @var non-negative-int $nodeId */
            $nodeId = $row[0];

            /** @var non-negative-int $nodeId */
            $parentId = $row[1];

            $treeId = $this->treeId;

            /** @var non-negative-int $nodeId */
            $index = $row[2];

            $dto = new TreenodeDto(
                $nodeId,
                $parentId,
                $treeId,
                $index
            );

            return $dto;
        };

        return array_map($callback, $nodeData);
    }
}