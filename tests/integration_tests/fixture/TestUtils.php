<?php

namespace pvcTests\struct\integration_tests\fixture;

use League\Container\Container;
use League\Container\Definition\DefinitionAggregate;
use League\Container\ReflectionContainer;
use pvc\interfaces\struct\dto\DtoInterface;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\interfaces\struct\tree\node\TreenodeInterface;
use pvc\interfaces\struct\tree\tree\TreeInterface;
use pvc\interfaces\validator\ValTesterInterface;
use pvc\struct\dto\DtoFactory;
use pvc\struct\dto\err\DtoInvalidArrayKeyException;
use pvc\struct\dto\err\DtoInvalidEntityGetterException;
use pvc\struct\tree\di\TreeDefinitions;
use pvc\struct\tree\dto\TreenodeDtoOrdered;
use pvc\struct\tree\dto\TreenodeDtoUnordered;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\tree\Tree;
use pvc\struct\tree\tree\TreeOrdered;
use ReflectionException;

/**
 * @template PayloadType of HasPayloadInterface
 */
class TestUtils
{
    protected Container $container;

    protected TreenodeConfigurationsFixture $fixture;

    public function __construct(TreenodeConfigurationsFixture $fixture)
    {
        $aggregate = new DefinitionAggregate(TreeDefinitions::makeDefinitions());
        $this->container = new Container($aggregate);
        /**
         * enable autowiring, which recursively evaluates arguments inside the definitions
         */
        $this->container->delegate(new ReflectionContainer());

        $this->fixture = $fixture;
    }

    /**
     * getNodeIdsFromNodeArray
     * @param TreenodeInterface $nodeArray
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

    public function testTreeSetup(bool $ordered) : TreeInterface
    {
        $treeId = 1;
        $dtoArray = $this->makeDtoArray($ordered);
        $tree = $this->makeTestTree($ordered);
        $tree->initialize($treeId, $dtoArray);
        return $tree;
    }

    /**
     * @param int $treeId
     * @return array
     * @throws ReflectionException
     * @throws DtoInvalidArrayKeyException
     * @throws DtoInvalidEntityGetterException
     */
    public function makeDtoArray(bool $ordered) : array
    {
        $nodeData = $this->fixture->getNodeData();
        $dtoFactory = $ordered ?
            new DtoFactory(TreenodeDtoOrdered::class, Treenode::class) :
            new DtoFactory(TreenodeDtoUnordered::class, Treenode::class);

        $callback = function(array $row) use ($dtoFactory, $ordered) : DtoInterface  {
            $arr = [];
            $arr['nodeId'] = $row[0];
            $arr['parentId'] = $row[1];
            $arr['treeId'] = null;
            $arr['payload'] = null;
            if ($ordered) {
                $arr['index'] = $row[2];
            }
            return $dtoFactory->makeDto($arr);
        };
        return array_map($callback, $nodeData);
    }

    public function makeTestTree(bool $ordered): TreeInterface
    {
        $classString = $ordered ? TreeOrdered::class : Tree::class;
        return $this->container->get($classString);
    }
}