<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\integration_tests\tree\fixture;

use pvc\interfaces\struct\tree\node\TreenodeAbstractInterface;
use pvc\interfaces\struct\tree\node_value_object\factory\TreenodeValueObjectFactoryInterface;
use pvc\interfaces\struct\tree\search\NodeDepthMapInterface;

/**
 * Class TreeTestFixture
 */
class TreenodeConfigurationsFixture
{
    protected int $treeId;

    /**
     * @var TreenodeValueObjectFactoryInterface
     */
    protected TreenodeValueObjectFactoryInterface $valueObjectFactory;

    protected NodeDepthMapInterface $depthMap;

    public function __construct(
        TreenodeValueObjectFactoryInterface $valueObjectFactory,
        NodeDepthMapInterface $depthMap
    ) {
        $this->treeId = 0;
        $this->valueObjectFactory = $valueObjectFactory;
        $this->depthMap = $depthMap;
    }

    /**
     * getTreeId
     * @return int
     */
    public function getTreeId(): int
    {
        return $this->treeId;
    }

    public function getDepthMap(): NodeDepthMapInterface
    {
        return $this->depthMap;
    }

    protected function transformNumericNodeData(array $row): array
    {
        $result = [];

        /**
         * nodeId
         */
        $result[0] = $row[0];

        /**
         * parentId
         */
        $result[1] = $row[1];

        /**
         * plug the treeid in
         */
        $result[2] = $this->treeId;

        /**
         * use the nodeId as the payload
         */
        $result[3] = $row[0];

        /**
         * index
         */
        $result[4] = $row[2];

        return $result;
    }

    public function makeValueObjectArray(): array
    {
        $nodeIdArray = $this->makeArrayOfNodeIdsForTree();
        $result = [];
        foreach ($nodeIdArray as $nodeData) {
            $valueObject = $this->valueObjectFactory->makeValueObject();
            $valueObject->hydrateFromNumericArray($this->transformNumericNodeData($nodeData));
            $result[] = $valueObject;
        }
        return $result;
    }

    /**
     * makeArrayOfNodeIdsFromArrayOfNodes
     * @param array<TreenodeAbstractInterface> $nodeArray
     * @return array<int>
     */
    public function makeArrayOfNodeIdsFromArrayOfNodes(array $nodeArray): array
    {
        $result = [];
        foreach ($nodeArray as $node) {
            $result[] = $node->getNodeId();
        }
        return $result;
    }


    /**
     * @function makeTreeStructureWithGoodData
     * @return array
     *
     * the order of the node data is scrambled in order to properly test the getTreeDepthFirst
     * and getTreeBreadthFirst methods for ordered trees.  The shape of each array is <nodeId, parentId, index>
     *
     *  Unordered:                              0
     *                                         / \
     *                                        1   2
     *                       3      4       5             6      7
     *                       8          9 10 11 12
     *
     *
     * Ordered:                                  0
     *                                          / \
     *                                         2   1
     *                                 6    7       5           3       4
     *                                          12 11 10 9      8
     */
    public function makeArrayOfNodeIdsForTree(): array
    {
        $a = [];
        $a[] = [0, null, 0];

        $a[] = [1, 0, 1];
        $a[] = [2, 0, 0];

        $a[] = [3, 1, 1];
        $a[] = [4, 1, 2];
        $a[] = [5, 1, 0];

        $a[] = [6, 2, 0];
        $a[] = [7, 2, 1];

        $a[] = [8, 3, 0];

        $a[] = [9, 5, 3];
        $a[] = [10, 5, 2];
        $a[] = [11, 5, 1];
        $a[] = [12, 5, 0];

        return $a;
    }

    public function makeNodeDepthMap(): void
    {
        $this->depthMap->setNodeDepth(0, 0);

        $this->depthMap->setNodeDepth(1, 1);
        $this->depthMap->setNodeDepth(2, 1);

        $this->depthMap->setNodeDepth(3, 2);
        $this->depthMap->setNodeDepth(4, 2);
        $this->depthMap->setNodeDepth(5, 2);

        $this->depthMap->setNodeDepth(6, 2);
        $this->depthMap->setNodeDepth(7, 2);

        $this->depthMap->setNodeDepth(8, 3);

        $this->depthMap->setNodeDepth(9, 3);
        $this->depthMap->setNodeDepth(10, 3);
        $this->depthMap->setNodeDepth(11, 3);
        $this->depthMap->setNodeDepth(12, 3);
    }

    public function makeExpectedNodeIdsRemainingIfNodeWithIdOneIsDeletedRecursively(): array
    {
        return [0, 2, 6, 7];
    }

    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneUnordered(): array
    {
        return [3, 4, 5];
    }

    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneOrdered(): array
    {
        return [5, 3, 4];
    }

    public function makeUnorderedBreadthFirstArrayOfAllNodeIds(): array
    {
        $expectedResult = [];
        for ($i = 0; $i <= 12; $i++) {
            $expectedResult[] = $i;
        }
        return $expectedResult;
    }

    public function makeOrderedBreadthFirstArrayOfAllNodeIds(): array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 2;
        $expectedResult[] = 1;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 5;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        $expectedResult[] = 12;
        $expectedResult[] = 11;
        $expectedResult[] = 10;
        $expectedResult[] = 9;
        $expectedResult[] = 8;
        return $expectedResult;
    }


    public function makeUnorderedBreadthFirstArrayStartingAtNodeid1(): array
    {
        $expectedResult = [];
        $expectedResult[] = 1;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        $expectedResult[] = 5;
        $expectedResult[] = 8;
        $expectedResult[] = 9;
        $expectedResult[] = 10;
        $expectedResult[] = 11;
        $expectedResult[] = 12;
        return $expectedResult;
    }

    public function makeOrderedBreadthFirstArrayStartingAtNodeid1(): array
    {
        $expectedResult = [];
        $expectedResult[] = 1;
        $expectedResult[] = 5;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        $expectedResult[] = 12;
        $expectedResult[] = 11;
        $expectedResult[] = 10;
        $expectedResult[] = 9;
        $expectedResult[] = 8;
        return $expectedResult;
    }

    public function makeUnorderedBreadthFirstArrayTwoLevelsStartingAtRoot(): array
    {
        $expectedResult = [];
        for ($i = 0; $i <= 7; $i++) {
            $expectedResult[] = $i;
        }
        return $expectedResult;
    }

    public function makeOrderedBreadthFirstArrayThreeLevelsStartingAtRoot(): array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 2;
        $expectedResult[] = 1;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 5;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        return $expectedResult;
    }

    public function makePreorderDepthFirstArrayThreeLevelsDeepStartingAtRoot(): array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 1;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        $expectedResult[] = 5;
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        return $expectedResult;
    }

    public function makePostorderDepthFirstArrayThreeLevelsDeepStartingAtRoot(): array
    {
        $expectedResult = [];
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        $expectedResult[] = 5;
        $expectedResult[] = 1;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 2;
        $expectedResult[] = 0;
        return $expectedResult;
    }


    public function makeUnorderedDepthFirstArrayOfBranchAtNodeid2(): array
    {
        $expectedResult = [];
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        return $expectedResult;
    }

    public function makeOrderedDepthFirstArrayOfBranchAtNodeid2(): array
    {
        // in the current configuration, this branch is the same whether ordered or unordered
        return $this->makeUnorderedDepthFirstArrayOfBranchAtNodeid2();
    }


    public function makeUnorderedPreorderDepthFirstArrayOfAllNodeIds(): array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 1;
        $expectedResult[] = 3;
        $expectedResult[] = 8;
        $expectedResult[] = 4;
        $expectedResult[] = 5;
        $expectedResult[] = 9;
        $expectedResult[] = 10;
        $expectedResult[] = 11;
        $expectedResult[] = 12;
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        return $expectedResult;
    }

    public function makeUnorderedPostOrderDepthFirstArrayOfAllNodeIds(): array
    {
        $expectedResult = [];
        $expectedResult[] = 8;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        $expectedResult[] = 9;
        $expectedResult[] = 10;
        $expectedResult[] = 11;
        $expectedResult[] = 12;
        $expectedResult[] = 5;
        $expectedResult[] = 1;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 2;
        $expectedResult[] = 0;
        return $expectedResult;
    }

    public function makeOrderedDepthFirstArrayOfAllNodeIds(): array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 1;
        $expectedResult[] = 5;
        $expectedResult[] = 12;
        $expectedResult[] = 11;
        $expectedResult[] = 10;
        $expectedResult[] = 9;
        $expectedResult[] = 3;
        $expectedResult[] = 8;
        $expectedResult[] = 4;
        return $expectedResult;
    }

    public function makeTreeWithNonExistentParentData(): array
    {
        $a = [];
        $a[] = [0, null, 0];

        $a[] = [1, 0, 0];
        $a[] = [2, 0, 1];

        $a[] = [3, 1, 0];
        $a[] = [4, 1, 1];
        $a[] = [5, 1, 2];

        // invalid parent node references
        $a[] = [6, 11, 0];
        $a[] = [7, 12, 0];

        return $this->makeValueObjectArray($a);
    }

    public function makeTreeWithMultipleRoots(): array
    {
        $a = [];
        $a[] = [0, null, 0];

        $a[] = [1, 0, 0];
        $a[] = [2, 0, 1];

        $a[] = [3, 1, 0];
        $a[] = [4, 1, 1];
        $a[] = [5, 1, 2];

        // multiple roots defined
        $a[] = [6, null, 0];
        $a[] = [7, null, 0];

        return $this->makeValueObjectArray($a);
    }

    public function makeTreeWithCircularParents(): array
    {
        $a = [];
        $a[] = [0, null, 0];
        $a[] = [1, 3, 0];
        $a[] = [2, 1, 0];
        $a[] = [3, 2, 0];

        return $this->makeValueObjectArray($a);
    }
}
