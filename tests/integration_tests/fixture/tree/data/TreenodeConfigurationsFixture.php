<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\integration_tests\fixture\tree\data;

/**
 * Class TreeTestFixture
 * @phpstan-type NodeData array{array<non-negative-int, non-negative-int|null, non-negative-int>}
 */
class TreenodeConfigurationsFixture
{
    /**
     * @function getNodeData
     * @return array
     *
     * the order of the node data is scrambled in order to properly test the getTreeDepthFirst
     * and getTreeBreadthFirst methods.  The shape of each array is <nodeId, parentId, index>
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
    /**
     * getNodeData
     * @return NodeData
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

    /**
     * makeExpectedNodeIdsRemainingIfNodeWithIdOneIsDeletedRecursively
     * @return array<non-negative-int>
     */
    public function makeExpectedNodeIdsRemainingIfNodeWithIdOneIsDeletedRecursively(
    ): array
    {
        return [0, 2, 6, 7];
    }

    /**
     * makeArrayOfAncestorsOfNodeWithNodeIdNine
     * @return array<non-negative-int>
     */
    public function makeArrayOfAncestorsOfNodeWithNodeIdNine(): array
    {
        return [9, 5, 1, 0];
    }

    /**
     * makeArrayOfAncestorsOfNodeWithNodeIdNineMaxLevelsTwo
     * @return array<non-negative-int>
     */
    public function makeArrayOfAncestorsOfNodeWithNodeIdNineMaxLevelsTwo(
    ): array
    {
        return [9, 5];
    }

    /***********************************************************************
     *                      Ordered Results
     ***********************************************************************/

    /**
     * Breadth First Search results
     */

    /**
     * makeOrderedBreadthFirstArrayOfAllNodeIds
     * @return array<non-negative-int>
     */
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

    /**
     * makeOrderedBreadthFirstArrayStartingAtNodeid1
     * @return array<non-negative-int>
     */
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

    /**
     * makeOrderedBreadthFirstArrayThreeLevelsStartingAtRoot
     * @return array<non-negative-int>
     */
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
     * DepthFirstSearchPreorder results
     */

    /**
     * makeOrderedPreorderDepthFirstArrayOfAllNodeIds
     * @return array<non-negative-int>
     */
    public function makeOrderedPreorderDepthFirstArrayOfAllNodeIds(): array
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

    /**
     * makeOrderedPreorderDepthFirstArrayThreeLevelsDeepStartingAtRoot
     * @return array<non-negative-int>
     */
    public function makeOrderedPreorderDepthFirstArrayThreeLevelsDeepStartingAtRoot(
    ): array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 1;
        $expectedResult[] = 5;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        return $expectedResult;
    }

    /**
     * Depth first Search Postorder results
     */

    /**
     * makeOrderedPostOrderDepthFirstArrayOfAllNodeIds
     * @return array<non-negative-int>
     */
    public function makeOrderedPostOrderDepthFirstArrayOfAllNodeIds(): array
    {
        $expectedResult = [];
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 2;
        $expectedResult[] = 12;
        $expectedResult[] = 11;
        $expectedResult[] = 10;
        $expectedResult[] = 9;
        $expectedResult[] = 5;
        $expectedResult[] = 8;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        $expectedResult[] = 1;
        $expectedResult[] = 0;
        return $expectedResult;
    }

    /**
     * makeOrderedPostorderDepthFirstArrayThreeLevelsDeepStartingAtRoot
     * @return array<non-negative-int>
     */
    public function makeOrderedPostorderDepthFirstArrayThreeLevelsDeepStartingAtRoot(
    ): array
    {
        $expectedResult = [];
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 2;
        $expectedResult[] = 5;
        $expectedResult[] = 3;
        $expectedResult[] = 4;
        $expectedResult[] = 1;
        $expectedResult[] = 0;
        return $expectedResult;
    }

    /************************************************************************
     *                  Unordered Results
     ************************************************************************/

    /**
     * makeUnorderedDepthFirstArrayOfBranchAtNodeid2
     * @return array<non-negative-int>
     */
    public function makeUnorderedDepthFirstArrayOfBranchAtNodeid2(): array
    {
        $expectedResult = [];
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        return $expectedResult;
    }

    /**
     * makeUnorderedDepthFirstArrayOfAllNodeIds
     * @return array<non-negative-int>
     */
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


    /**
     * makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneUnordered
     * @return array<non-negative-int>
     */
    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneUnordered(
    ): array
    {
        return [3, 4, 5];
    }


    /**
     * makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneOrdered
     * @return array<non-negative-int>
     */
    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneOrdered(
    ): array
    {
        return [5, 3, 4];
    }


    /**
     * makeUnorderedBreadthFirstArrayOfAllNodeIds
     * @return array<non-negative-int>
     */
    public function makeUnorderedBreadthFirstArrayOfAllNodeIds(): array
    {
        $expectedResult = [];
        for ($i = 0; $i <= 12; $i++) {
            $expectedResult[] = $i;
        }
        return $expectedResult;
    }


    /**
     * makeUnorderedBreadthFirstArrayStartingAtNodeid1
     * @return array<non-negative-int>
     */
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

    /**
     * makeUnorderedBreadthFirstArrayTwoLevelsStartingAtRoot
     * @return array<non-negative-int>
     */
    public function makeUnorderedBreadthFirstArrayTwoLevelsStartingAtRoot(
    ): array
    {
        $expectedResult = [];
        for ($i = 0; $i <= 7; $i++) {
            $expectedResult[] = $i;
        }
        return $expectedResult;
    }
}
