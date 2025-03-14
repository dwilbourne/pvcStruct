<?php

namespace pvcTests\struct\integration_tests\tree\fixture;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\ReflectionContainer;
use pvc\interfaces\struct\dto\DtoInterface;
use pvc\interfaces\struct\tree\dto\TreenodeDtoFactoryInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\dto\err\DtoInvalidPropertyValueException;
use pvc\struct\tree\di\TreeDefinitions;
use pvc\struct\tree\tree\Tree;
use ReflectionException;

class TestUtils
{
    protected Container $container;

    public function __construct(bool $ordered = false)
    {
        // $payloadTester = ???
        // $aggregate = new DefinitionAggregate(TreeDefinitions::makeDefinitions($ordered, $payloadTester));

        $aggregate = new DefinitionAggregate(TreeDefinitions::makeDefinitions($ordered));
        $this->container = new Container($aggregate);
        /**
         * enable autowiring, which recursively evaluates arguments inside the definitions
         */
        $this->container->delegate(new ReflectionContainer());
    }

    /**
     * getNodeIdsFromNodeArray
     * @param array<TreenodeInterface<int>> $nodeArray
     * @return array<int>
     */
    public static function getNodeIdsFromNodeArray(array $nodeArray): array
    {
        $callback = function (TreenodeInterface $node) { return $node->getNodeId(); };
        /**
         * array_map will preserve keys so just return the array values
         */
        return array_values(array_map($callback, $nodeArray));
    }

    public function testTreeSetup(TreenodeConfigurationsFixture $fixture, bool $ordered = false) : Tree
    {
        $treeId = 1;
        $dtoArray = $this->makeDtoArray($fixture);
        $tree = $this->makeTestTree();
        $tree->initialize($treeId, $dtoArray);
        return $tree;
    }

    /**
     * @param int $treeId
     * @return array
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     * @throws DtoInvalidPropertyValueException
     */
    public function makeDtoArray(TreenodeConfigurationsFixture $fixture) : array
    {
        $nodeData = $fixture->getNodeData();
        $dtoFactory = $this->container->get(TreenodeDtoFactoryInterface::class);

        $callback = function(array $row) use ($dtoFactory) : DtoInterface  {
            $arr = [];
            $arr['nodeId'] = $row[0];
            $arr['parentId'] = $row[1];
            $arr['treeId'] = null;
            $arr['payload'] = null;
            $arr['index'] = $row[2];
            return $dtoFactory->makeDto($arr);
        };
        return array_map($callback, $nodeData);
    }

    public function makeTestTree(): TreeInterface
    {
        return $this->container->get(TreeInterface::class);
    }
}