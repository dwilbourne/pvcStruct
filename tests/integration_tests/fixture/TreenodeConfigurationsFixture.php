<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\integration_tests\fixture;

/**
 * Class TreeTestFixture
 */
class TreenodeConfigurationsFixture
{
    /**
     * @function makeArrayOfNodeIdsForTree
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
    public function getNodeData(): array
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

    public function makeExpectedNodeIdsRemainingIfNodeWithIdOneIsDeletedRecursively(
    ): array
    {
        return [0, 2, 6, 7];
    }

    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneUnordered(
    ): array
    {
        return [3, 4, 5];
    }

    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneOrdered(
    ): array
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

    public function makeUnorderedBreadthFirstArrayTwoLevelsStartingAtRoot(
    ): array
    {
        $expectedResult = [];
        for ($i = 0; $i <= 7; $i++) {
            $expectedResult[] = $i;
        }
        return $expectedResult;
    }

    public function makeOrderedBreadthFirstArrayThreeLevelsStartingAtRoot(
    ): array
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

    /**
     * makeOrderedBreadthFirstArrayThreeLevelsStartingAtRootForEvenNumberedNodes
     *
     * @return array
     * let's say that 0 is an even number for the moment
     */
    public function makeOrderedBreadthFirstArrayThreeLevelsStartingAtRootForEvenNumberedNodes(
    ): array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 4;
        return $expectedResult;
    }

    public function makePreorderDepthFirstArrayThreeLevelsDeepStartingAtRoot(
    ): array
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

    public function makePreorderDepthFirstArrayThreeLevelsDeepStartingAtRootForEvenNumberedNodes(
    ): array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 4;
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        return $expectedResult;
    }

    public function makePostorderDepthFirstArrayThreeLevelsDeepStartingAtRoot(
    ): array
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

    public function makeOrderedDepthFirstArrayOfBranchAtNodeid2(): array
    {
        // in the current configuration, this branch is the same whether ordered or unordered
        return $this->makeUnorderedDepthFirstArrayOfBranchAtNodeid2();
    }

    public function makeUnorderedDepthFirstArrayOfBranchAtNodeid2(): array
    {
        $expectedResult = [];
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        return $expectedResult;
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

    public function makeUnorderedDepthFirstArrayOfAllNodeIds(): array
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

        return $this->makeDtoArray($a);
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

        return $this->makeDtoArray($a);
    }

    public function makeTreeWithCircularParents(): array
    {
        $a = [];
        $a[] = [0, null, 0];
        $a[] = [1, 3, 0];
        $a[] = [2, 1, 0];
        $a[] = [3, 2, 0];

        return $this->makeDtoArray($a);
    }
}
