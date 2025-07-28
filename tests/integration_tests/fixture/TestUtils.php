<?php

namespace pvcTests\struct\integration_tests\fixture;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\ReflectionContainer;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\tree\di\TreeDefinitions;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\dto\TreenodeDtoOrdered;
use pvc\struct\tree\tree\TreeOrdered;
use pvc\struct\tree\tree\TreeUnordered;
use ReflectionException;

class TestUtils
{
    protected Container $container;

    protected TreenodeConfigurationsFixture $fixture;

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
     * @param  TreenodeInterface  $nodeArray
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

    public function testTreeSetup(bool $ordered): TreeInterface
    {
        $treeId = 1;
        $dtoArray = $this->makeDtoArray($ordered);
        $tree = $this->makeTestTree($ordered);
        $tree->initialize($treeId);
        $tree->hydrate($dtoArray);
        return $tree;
    }

    /**
     * @param  int  $treeId
     *
     * @return array<TreenodeDto>|array<TreenodeDtoOrdered>
     * @throws ReflectionException
     */
    public function makeDtoArray(bool $ordered): array
    {
        $nodeData = $this->fixture->getNodeData();
        $callback = function (array $row) use ($ordered
        ): TreenodeDto|TreenodeDtoOrdered {
            $nodeId = $row[0];
            $parentId = $row[1];
            $treeId = null;
            if ($ordered) {
                $index = $row[2];
            }

            if ($ordered) {
                $dto = new TreenodeDtoOrdered(
                    $nodeId,
                    $parentId,
                    $treeId,
                    $index
                );
            } else {
                $dto = new TreenodeDto($nodeId, $parentId, $treeId);
            }
            return $dto;
        };
        return array_map($callback, $nodeData);
    }

    public function makeTestTree(bool $ordered): TreeInterface
    {
        $classString = $ordered ? TreeOrdered::class : TreeUnordered::class;
        return $this->container->get($classString);
    }
}