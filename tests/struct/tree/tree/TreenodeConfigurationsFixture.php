<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\tree\tree;

/**
 * Class TreeTestFixture
 */
class TreenodeConfigurationsFixture
{
	protected int $treeId = 1;

	/**
	 * getTreeId
	 * @return int
	 */
	public function getTreeId() : int {
		return $this->treeId;
	}

	/**
	 * @function makeNodeDataAssociativeArray
	 * @param array $structure
	 * @return array
	 */
	public function makeNodeDataAssociativeArray(array $nodeDataArray): array
	{
		$result = [];
		foreach ($nodeDataArray as $rowData) {
			$result[] = $this->makeRow($rowData);
		}
		return $result;
	}

	/**
	 * Makes an associative array of node data.
	 *
	 * The order of the data in the argument is nodeid, parentid, index.
	 * Index is not used in the computations for unordered trees but this fixture produces data that is used by both
	 * ordered and unordered tree tests, so the index goes along for the ride half the time.
	 *
	 * @function makeRow
	 * @param array $data
	 * @return array
	 */
	public function makeRow(array $data)
	{
		return [
			'nodeid' => $data[0],
			'parentid' => $data[1],
			'treeid' => $this->getTreeId(),
			'index' => $data[2],
			'value' => null
		];
	}

	public function makeRootNodeRowWithGoodData() : array
	{
		return $this->makeRow([0, null, 0]);
	}

	public function makeSecondRootNodeRowWithDifferentNodeId() : array
	{
		return $this->makeRow([1, null, 0]);
	}

	public function makeRootNodeRowWithBadTreeId() : array
	{
		return [
			'nodeid' => 0,
			'parentid' => null,
			'treeid' => $this->getTreeId() + 1,
			'index' => 0,
			'value' => null
		];
	}

	/**
	 * this node can be used as a second node in the tree after the root because the parentid = 0 and the root node
	 * above has nodeid = 0.
	 *
	 * makeSingleNodeRowWithRootAsParent
	 * @return array
	 */
	public function makeSingleNodeRowWithRootAsParent() : array
	{
		return [
			'nodeid' => 2,
			'parentid' => 0,
			'treeid' => $this->getTreeId(),
			'index' => 0,
			'value' => null
		];
	}

	/**
	 * makeNodeRowWithNullNodeId
	 * @return array
	 */
	public function makeNodeRowWithNullNodeId() : array
	{
		return $this->makeRow([null, null, 0]);
	}


    /**
     * @function makeTreeStructureWithGoodData
     * @return array
     *
     * the order of the node data is scrambled in order to properly test the getTreeDepthFirst
     * and getTreeBreadthFirst methods for ordered trees
     */
    public function makeArrayOfNodeIdsForTree(): array
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


        return $this->makeNodeDataAssociativeArray($a);
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

    public function makeOrderedBreadthFirstArrayTwoLevelsStartingAtRoot(): array
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

        return $this->makeNodeDataAssociativeArray($a);
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

        return $this->makeNodeDataAssociativeArray($a);
    }

    public function makeTreeWithCircularParents(): array
    {
        $a = [];
        $a[] = [0, null, 0];
        $a[] = [1, 3, 0];
        $a[] = [2, 1, 0];
        $a[] = [3, 2, 0];

        return $this->makeNodeDataAssociativeArray($a);
    }

    public function makeArrayOfGoodDataLeafNodeIds(): array
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

    public function makeArrayOfGoodDataInteriorNodeIds(): array
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
