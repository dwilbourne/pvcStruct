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
use pvc\struct\tree\node\TreenodeFactory;
use pvc\struct\tree\node\TreenodeFactoryOrdered;
use pvc\struct\tree\node\TreenodeFactoryUnordered;
use pvc\struct\tree\node\TreenodeOrdered;
use pvc\struct\tree\node\TreenodeUnordered;
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

    public function testTreeSetup(bool $ordered, bool $makeNodes = false): TreeInterface
    {
        $treeId = 1;
        $inputArray = $this->makeInputArray($ordered, $makeNodes);
        $tree = $this->makeTestTree($ordered);
        $tree->initialize($treeId);
        $tree->hydrate($inputArray);
        return $tree;
    }

    /**
     * @return array<TreenodeDto>|array<TreenodeDtoOrdered>
     * @throws ReflectionException
     */
    public function makeInputArray(bool $ordered, bool $makeNodes = false): array
    {
        $nodeData = $this->fixture->getNodeData();
        $callback = function (array $row) use ($ordered, $makeNodes
        ): TreenodeDto|TreenodeDtoOrdered|TreenodeUnordered|TreenodeOrdered {
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

            if ($makeNodes) {
                $factory = $ordered ?
                    $this->container->get(TreenodeFactoryOrdered::class) :
                    $this->container->get(TreenodeFactoryUnordered::class);
                $result = $factory->makeNode();
                $result->hydrate($dto);
            } else {
                $result = $dto;
            }

            return $result;
        };
        return array_map($callback, $nodeData);
    }

    public function makeTestTree(bool $ordered): TreeInterface
    {
        $classString = $ordered ? TreeOrdered::class : TreeUnordered::class;
        return $this->container->get($classString);
    }
}