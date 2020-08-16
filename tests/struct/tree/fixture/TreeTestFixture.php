<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree\fixture;

use pvc\struct\tree\node\Treenode;

/**
 * Class TreeTestFixture
 */
class TreeTestFixture
{
    /** @phpstan-ignore-next-line */
    protected $testInstance;

    protected int $treeid;

    /**
     * TreeTestFixture constructor.
     * @param mixed $testInstance
     * @param int $treeid
     */
    public function __construct($testInstance, int $treeid)
    {
        $this->testInstance = $testInstance;
        $this->treeid = $treeid;
    }

    /**
     * @function makeTree
     * @param array $structure
     * @return array
     */
    private function makeTree(array $structure): array
    {
        $result = [];
        foreach ($structure as $rowData) {
            $row = $this->makeRow($rowData);
            $result[] = $this->makeNode($row);
        }
        return $result;
    }

    /**
     * @function makeNode
     * @param mixed $row
     * @return mixed
     */
    private function makeNode($row)
    {
        $nodeid = $row['nodeid'];
        $node = $this->testInstance->makeNode($nodeid);
        $node->hydrate($row);
        return $node;
    }

    /**
     * @function makeRow
     * @param array $data
     * @return array
     */
    private function makeRow(array $data)
    {
        return [
            'nodeid' => $data[0],
            'parentid' => $data[1],
            'treeid' => $this->treeid,
            'index' => $data[2],
            'value' => null
        ];
    }

    /**
     * @function getRootNodeId
     * @return int
     */
    public function getRootNodeId() : int
    {
        return 0;
    }

    /**
     * @function makeTreeStructureWithGoodData
     * @return array
     *
     * the order of the node data is scrambled in order to properly test the getTreeDepthFirst
     * and getTreeBreadthFirst methods
     */
    public function makeTreeWithGoodData() : array
    {
        $a = [];
        $a[] = [0, null, 0];

        $a[] = [9, 5, 3];
        $a[] = [10, 5, 2];
        $a[] = [11, 5, 1];
        $a[] = [12, 5, 0];

        $a[] = [3, 1, 1];
        $a[] = [4, 1, 2];
        $a[] = [5, 1, 0];

        $a[] = [6, 2, 0];
        $a[] = [7, 2, 1];

        $a[] = [1, 0, 1];
        $a[] = [2, 0, 0];

        $a[] = [8, 3, 0];


        return $this->makeTree($a);
    }

    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneUnordered() : array
    {
        return [3, 4, 5];
    }

    public function makeArrayOfNodeIdsChildrenOfNodeWithIdEqualToOneOrdered() : array
    {
        return [5, 3, 4];
    }

    public function makeUnorderedBreadthFirstArrayOfAllNodeIds() : array
    {
        $expectedResult = [];
        for ($i = 0; $i <= 12; $i++) {
            $expectedResult[] = $i;
        }
        return $expectedResult;
    }

    public function makeOrderedBreadthFirstArrayOfAllNodeIds() : array
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


    public function makeUnorderedBreadthFirstArrayStartingAtNodeid1() : array
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

    public function makeOrderedBreadthFirstArrayStartingAtNodeid1() : array
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

    public function makeUnorderedBreadthFirstArrayTwoLevelsStartingAtRoot() : array
    {
        $expectedResult = [];
        for ($i = 0; $i <= 7; $i++) {
            $expectedResult[] = $i;
        }
        return $expectedResult;
    }

    public function makeOrderedBreadthFirstArrayTwoLevelsStartingAtRoot() : array
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


    public function makeUnorderedDepthFirstArrayOfBranchAtNodeid2() : array
    {
        $expectedResult = [];
        $expectedResult[] = 2;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        return $expectedResult;
    }

    public function makeOrderedDepthFirstArrayOfBranchAtNodeid2() : array
    {
        // in the current configuration, this branch is the same whether ordered or unordered
        return $this->makeUnorderedDepthFirstArrayOfBranchAtNodeid2();
    }


    public function makeUnorderedDepthFirstArrayOfAllNodeIds() : array
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

    public function makeOrderedDepthFirstArrayOfAllNodeIds() : array
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

    public function makeTreeWithNonExistentParentData() : array
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

        return $this->makeTree($a);
    }

    public function makeTreeWithMultipleRoots() : array
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

        return $this->makeTree($a);
    }

    public function makeTreeWithCircularParents() : array
    {
        $a = [];
        $a[] = [0, null, 0];
        $a[] = [1, 3, 0];
        $a[] = [2, 1, 0];
        $a[] = [3, 2, 0];

        return $this->makeTree($a);
    }

    public function makeArrayOfGoodDataLeafNodeIds() : array
    {
        $expectedResult = [];
        $expectedResult[] = 4;
        $expectedResult[] = 6;
        $expectedResult[] = 7;
        $expectedResult[] = 8;
        $expectedResult[] = 9;
        $expectedResult[] = 10;
        $expectedResult[] = 11;
        $expectedResult[] = 12;
        return $expectedResult;
    }

    public function makeArrayOfGoodDataInteriorNodeIds() : array
    {
        $expectedResult = [];
        $expectedResult[] = 0;
        $expectedResult[] = 1;
        $expectedResult[] = 2;
        $expectedResult[] = 3;
        $expectedResult[] = 5;
        return $expectedResult;
    }
}
