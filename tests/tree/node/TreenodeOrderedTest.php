<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace pvcTests\struct\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\lists\ListOrderedInterface;
use pvc\interfaces\struct\tree\node\TreenodeOrderedInterface;
use pvc\interfaces\struct\tree\tree\TreeOrderedInterface;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\SetChildrenException;
use pvc\struct\tree\node\TreenodeOrdered;

use stdClass;

/**
 * Class TreenodeOrderedTest
 */
class TreenodeOrderedTest extends TestCase
{
    protected TreeOrderedInterface $tree;

    protected TreenodeOrderedInterface $parent;
    protected ListOrderedInterface|MockObject $parentChildList;

    protected TreenodeOrderedInterface $node;
    protected ListOrderedInterface|MockObject $nodeChildList;

    protected TreenodeOrderedInterface $childOne;
    protected ListOrderedInterface|MockObject $childOneChildList;

    protected TreenodeOrderedInterface $childTwo;
    protected ListOrderedInterface|MockObject $childTwoChildList;

    /**
     * setUp
     */
    public function setUp(): void
    {
        $treeid = 2;
        $this->tree = $this->createStub(TreeOrderedInterface::class);
        $this->tree->method('getTreeId')->with()->willReturn($treeid);

        $this->parentChildList = $this->createMock((ListOrderedInterface::class));
        $this->nodeChildList = $this->createMock((ListOrderedInterface::class));
        $this->childOneChildList = $this->createMock((ListOrderedInterface::class));
        $this->childTwoChildList = $this->createMock((ListOrderedInterface::class));

        $this->parentChildList->method('isEmpty')->willReturn(true);
        $this->nodeChildList->method('isEmpty')->willReturn(true);
        $this->childOneChildList->method('isEmpty')->willReturn(true);
        $this->childTwoChildList->method('isEmpty')->willReturn(true);

        $this->parent = $this->makeNode(0, null, $this->parentChildList);
        $this->node = $this->makeNode(1, 0, $this->nodeChildList);
        $this->childOne = $this->makeNode(8, 1, $this->childOneChildList);
        $this->childTwo = $this->makeNode(6, 1, $this->childTwoChildList);

        $this->tree->method('getRoot')->with()->willReturn($this->parent);
    }

    protected function mockIterator(MockObject $mock, array $items): MockObject
    {
        $iteratorData = new stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $mock->method('rewind')->with()->will($this->returnCallback(
            function () use ($iteratorData) {
                $iteratorData->position = 0;
            }
        ));

        $mock->method('current')->with()->will($this->returnCallback(
            function () use ($iteratorData) {
                return $iteratorData->array[$iteratorData->position];
            }
        ));

        $mock->method('key')->with()->will($this->returnCallback(
            function () use ($iteratorData) {
                return $iteratorData->position;
            }
        ));

        $mock->method('next')->with()->will($this->returnCallback(
            function () use ($iteratorData) {
                $iteratorData->position++;
            }
        ));

        $mock->method('valid')->with()->will($this->returnCallback(
            function () use ($iteratorData) {
                return isset($iteratorData->array[$iteratorData->position]);
            }
        ));

        return $mock;
    }

    protected function mockCountable(MockObject $mock, int $result): MockObject
    {
        $mock->method('count')->withAnyParameters()->willReturn($result);
        return $mock;
    }

    /**
     * @function testConstruct
     * @covers \pvc\struct\tree\node\TreenodeOrdered::__construct
     */
    public function testConstruct() : void
    {
        $nodeid = 1;
        $node = new TreenodeOrdered($nodeid);
        self::assertEquals($nodeid, $node->getNodeId());
    }

    /**
     * testSetGetHydrationIndex
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setHydrationIndex
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getHydrationIndex
     */
    public function testSetGetHydrationIndex() : void
    {
        $hydrationIndex = 4;
        $this->node->setHydrationIndex($hydrationIndex);
        self::assertEquals($hydrationIndex, $this->node->getHydrationIndex());
    }

    /**
     * testHydrate
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @throws \pvc\struct\tree\err\InvalidNodeValueException
     * @covers \pvc\struct\tree\node\TreenodeOrdered::hydrate
     */
    public function testHydrate(): void
    {
        $row = ['nodeid' => 0, 'index' => 5, 'parentid' => null, 'treeid' => 2, 'value' => 'some string'];
        $node = new TreenodeOrdered(0);
        $node->hydrate($row);
        self::assertEquals(0, $node->getNodeId());
        self::assertEquals(5, $node->getHydrationIndex());
        self::assertNull($node->getParentId());
        self::assertEquals(2, $node->getTreeId());
        self::assertEquals('some string', $node->getValue());
    }

    /**
     * testDehydrate
     * @covers \pvc\struct\tree\node\TreenodeOrdered::dehydrate
     */
    public function testDehydrate(): void
    {
        $expectedResult = ['nodeid' => 0, 'parentid' => null, 'treeid' => 2, 'index' => 0, 'value' => null];
        self::assertEqualsCanonicalizing($expectedResult, $this->parent->dehydrate());
    }

    /**
     * testSetReferencesThrowsExceptionWhenNodeIsNotInTree
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setReferences
     */
    public function testSetReferencesThrowsExceptionWhenNodeIsNotInTree(): void
    {
        $this->tree->method('getNode')->with($this->node->getNodeId())->willReturn(null);
        $this->expectException(NodeNotInTreeException::class);
        $this->node->setReferences($this->tree);
    }

    /**
     * testSetReferencesWorksWhenParentIdIsNull
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setReferences
     */
    public function testSetReferencesWorksWhenParentIdIsNull(): void
    {
        $this->tree->method('getNode')->with($this->parent->getNodeId())->willReturn($this->parent);
        $this->parent->setReferences($this->tree);
        self::assertEquals($this->tree, $this->parent->getTree());
    }

    /**
     * testSetReferencesWorksWhenParentIsNotNull
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setReferences
     */
    public function testSetReferencesWorksWhenParentIsNotNull(): void
    {
        $this->tree->expects($this->exactly(2))
                ->method('getNode')
                ->withConsecutive([$this->node->getNodeId()], [$this->parent->getNodeId()])
                ->willReturn($this->node, $this->parent);

        $childList = $this->createMock(ListOrderedInterface::class);
        $childList->method('isEmpty')->willReturn(true);
        $childList->expects($this->once())->method('push');

        $this->parent->setChildList($childList);
        $this->node->setReferences($this->tree);
        self::assertEquals($this->tree, $this->node->getTree());
    }

    /**
     * setAllReferences
     */
    public function setAllReferences(): void
    {
        $map = [
            [$this->parent->getNodeId(), $this->parent],
            [$this->node->getNodeId(), $this->node],
            [$this->childOne->getNodeId(), $this->childOne],
            [$this->childTwo->getNodeId(), $this->childTwo],
            ];

        $this->tree->method('getNode')->will($this->returnValueMap($map));

        $this->parent->setReferences($this->tree);
        $this->node->setReferences($this->tree);
        $this->childOne->setReferences($this->tree);
        $this->childTwo->setReferences($this->tree);
    }

    /**
     * makeNode
     * @param int $nodeid
     * @param int|null $parentid
     * @return TreenodeOrderedInterface
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     */
    public function makeNode(int $nodeid, int $parentid = null, ListOrderedInterface $childList):
    TreenodeOrderedInterface
    {
        $node = new TreenodeOrdered($nodeid);
        $node->setParentId($parentid);
        $node->setTreeId($this->tree->getTreeId());
        $node->setChildList($childList);
        return $node;
    }

    /**
     * testGetIndexReturnsNullWhenNodeIsNotInTree
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getIndex
     */
    public function testGetIndexReturnsNullWhenNodeIsNotInTree(): void
    {
        /** references have not been set, so no reference to tree */
        self::assertNull($this->node->getIndex());
    }


    /**
     * testGetIndexReturnsZeroWhenThereAreNoSiblings
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getIndex
     */
    public function testGetIndexReturnsZeroWhenThereAreNoSiblings(): void
    {
        $this->setAllReferences();
        /**
         * parent is the root and has no parentidso getIndex returns 0
         */
        self::assertEquals(0, $this->parent->getIndex());
    }

    /**
     * testGetIndexReturnsProperIndexWithSiblings
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getIndex
     */
    public function testGetIndexReturnsProperIndexWithSiblings(): void
    {
        $this->setAllReferences();
        $this->nodeChildList = $this->mockIterator($this->nodeChildList, [$this->childOne, $this->childTwo]);
        /**
         * their indices depend on the order in which they were added to the tree.  SetAllReferences adds ChildOne
         * first and ChildTwo second.
         */
        self::assertEquals(0, $this->childOne->getIndex());
        self::assertEquals(1, $this->childTwo->getIndex());
    }

    /**
     * testSetIndexThrowsExceptionWhenTreeIsNotSet
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setIndex
     */

    public function testSetIndexThrowsExceptionWhenTreeIsNotSet(): void
    {
        $this->expectException(NodeNotInTreeException::class);
        /** references have not been set, so no reference to tree */
        $this->node->setIndex(1);
    }

    /**
     * testSetIndexDoesNothingWhenThereAreNoSiblings
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setIndex
     */
    public function testSetIndexDoesNothingWhenThereAreNoSiblings(): void
    {
        $this->setAllReferences();
        $existingIndex = $this->parent->getIndex();
        $this->parent->setIndex(4);
        self::assertEquals($existingIndex, $this->parent->getIndex());
    }

    /**
     * testSetIndex
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setIndex
     */
    public function testSetIndex(): void
    {
        $this->setAllReferences();
        $this->nodeChildList = $this->mockIterator($this->nodeChildList, [$this->childOne, $this->childTwo]);

        $this->nodeChildList->expects($this->once())
            ->method('changeIndex')
            ->with($this->childOne->getIndex(), $this->childTwo->getIndex());

        self::assertEquals(0, $this->childOne->getIndex());
        self::assertEquals(1, $this->childTwo->getIndex());

        $this->childOne->setIndex(1);

        // it takes an integration test to verify that the indices got changed properly because it's the ordered list
        // doing the work.  So that is actually covered by the unit test on the OrderedList class.
    }

    /**
     * testUnsetReferencesThrowsExceptionOnInteriorNode
     * @covers \pvc\struct\tree\node\TreenodeOrdered::unsetReferences
     */
    public function testUnsetReferencesThrowsExceptionOnInteriorNode(): void
    {
        $this->setAllReferences();
        $this->mockCountable($this->node->getChildren(), 2);
        $this->expectException(DeleteInteriorNodeException::class);
        $this->node->unsetReferences();
    }

    /**
     * Normally, unsetReferences wants to get the parent to delete the node from its list of children.
     *
     * But if the node's getIndex method returns null, it is because its getSiblings method returns null.  This is turn
     * means that the node has no parent set and it is not really part of a tree.  And it should not have any
     * references set.  Nevertheless, unsetReferences should succeed and the node should be empty.  So this test
     * tests the case where getIndex returns null.
     *
     * testUnsetReferences
     * @covers \pvc\struct\tree\node\TreenodeOrdered::unsetReferences
     */
    public function testUnsetReferencesWithGetIndexReturnsNull(): void
    {
        $this->setAllReferences();

        $this->node->unsetReferences();

        self::assertNull($this->node->getParent());
        self::assertNull($this->node->getParentId());
        self::assertNull($this->node->getTree());
        self::assertNull($this->node->getTreeId());
        self::assertNull($this->node->getIndex());
        // nodeid remains set
        self::assertEquals(1, $this->node->getNodeId());
    }

    /**
     * The easiest way for getIndex to return a non-null value is to stub the getChildren method of the parent to
     * return true for the isEmpty method.  The code concludes that if the child list is empty, then the index is 0.
     *
     * testUnsetReferencesWithGetIndexReturnsZero
     * @throws \Exception
     * @covers \pvc\struct\tree\node\TreenodeOrdered::unsetReferences
     */
    public function testUnsetReferencesWithGetIndexReturnsZero(): void
    {
        $this->setAllReferences();

        $this->parent->getChildren()->method('isEmpty')->willReturn(true);

        $this->node->unsetReferences();

        self::assertNull($this->node->getParent());
        self::assertNull($this->node->getParentId());
        self::assertNull($this->node->getTree());
        self::assertNull($this->node->getTreeId());
        self::assertNull($this->node->getIndex());
        // nodeid remains set
        self::assertEquals(1, $this->node->getNodeId());
    }


    /**
     * testGetTree
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getTree
     */
    public function testGetTree(): void
    {
        self::assertNull($this->node->getTree());
        $this->setAllReferences();
        self::assertSame($this->tree, $this->node->getTree());
    }

    /**
     * testGetParent
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getParent
     */
    public function testGetParent() : void
    {
        self::assertNull($this->node->getParent());
        $this->setAllReferences();
        self::assertSame($this->parent, $this->node->getParent());
    }

    /**
     * testGetChildReturnsNullWithNodeidNotInListOfChildren
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getChild
     */
    public function testGetChildReturnsNullWithNodeidNotInListOfChildren() : void
    {
        $this->setAllReferences();
        $this->nodeChildList = $this->mockIterator($this->nodeChildList, [$this->childOne, $this->childTwo]);
        $nonExistentNodeId = 17;
        self::assertNull($this->node->getChild($nonExistentNodeId));
    }

    /**
     * testGetChild
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getChild
     */
    public function testGetChild(): void
    {
        $this->setAllReferences();
        $this->nodeChildList = $this->mockIterator($this->nodeChildList, [$this->childOne, $this->childTwo]);

        self::assertEquals($this->childOne, $this->node->getChild($this->childOne->getNodeId()));
        self::assertEquals($this->childTwo, $this->node->getChild($this->childTwo->getNodeId()));
    }

    /**
     * testSetGetChildren
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getChildren
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setChildList
     */
    public function testSetGetChildren() : void
    {
        $childList = $this->createMock(ListOrderedInterface::class);
        $childList->method('isEmpty')->willReturn(true);
        $node = new TreenodeOrdered(0);
        $node->setChildList($childList);
        self::assertSame($childList, $node->getChildren());
    }

    /**
     * testSetChildrenThrowsExceptionIfListIsNotEmpty
     * @covers \pvc\struct\tree\node\TreenodeOrdered::setChildList
     */
    public function testSetChildrenThrowsExceptionIfListIsNotEmpty() : void
    {
        $childList = $this->createMock(ListOrderedInterface::class);
        $childList->method('isEmpty')->willReturn(false);
        $node = new TreenodeOrdered(0);
        $this->expectException(SetChildrenException::class);
        $node->setChildList($childList);
    }

    /**
     * testGetChildrenArray
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getChildrenArray
     */
    public function testGetChildrenArray(): void
    {
        $this->setAllReferences();
        $this->nodeChildList->expects($this->once())->method('getElements')->willReturn(
            [$this->childOne, $this->childTwo]
        );
        $expectedResult = [$this->childOne, $this->childTwo];
        self::assertEqualsCanonicalizing($expectedResult, $this->node->getChildrenArray());
    }

    /**
     * testGetSiblingsReturnsNullWithParentNotSet
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getChildrenArray
     */
    public function testGetSiblingsReturnsNullWithParentNotSet() : void
    {
        self::assertNull($this->parent->getSiblings());
    }

    /**
     * testGetSiblingsReturnsList
     * @covers \pvc\struct\tree\node\TreenodeOrdered::getSiblings
     */
    public function testGetSiblingsReturnsList() : void
    {
        $this->setAllReferences();
        self::assertInstanceOf(ListOrderedInterface::class, $this->node->getSiblings());
    }

    /**
     * testIsLeaf
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isLeaf
     */
    public function testIsLeafReturnsTrueWhenCountOfChildrenEqualsZero(): void
    {
        $this->setAllReferences();
        $this->mockCountable($this->childOne->getChildren(), 0);
        self::assertTrue($this->childOne->isLeaf());
    }

    /**
     * IsLeafReturnsFalseWhenCountOfChildrenIsGreaterThanZero
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isLeaf
     */
    public function IsLeafReturnsFalseWhenCountOfChildrenIsGreaterThanZero() : void
    {
        $this->setAllReferences();
        $this->mockCountable($this->node->getChildren(), 2);
        self::assertFalse($this->node->isLeaf());
    }

    /**
     * testIsInteriorNodeReturnsFalseWhenCountOfChildrenEqualsZero
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isInteriorNode
     */
    public function testIsInteriorNodeReturnsFalseWhenCountOfChildrenEqualsZero(): void
    {
        $this->setAllReferences();
        $this->mockCountable($this->childOne->getChildren(), 0);
        self::assertFalse($this->childOne->isInteriorNode());
    }

    /**
     * testIsInteriorNodeReturnsTrueWhenCountOfChildrenIsGreaterThanZero
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isInteriorNode
     */
    public function testIsInteriorNodeReturnsTrueWhenCountOfChildrenIsGreaterThanZero() : void
    {
        $this->setAllReferences();
        $this->mockCountable($this->node->getChildren(), 2);
        self::assertTrue($this->node->isInteriorNode());
    }

    /**
     * testIsRootReturnsFalseWhenTreeIsNotSet
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isRoot
     */
    public function testIsRootReturnsFalseWhenTreeIsNotSet(): void
    {
        // node is not root if tree is not set, even if parent is null
        self::assertFalse($this->parent->isRoot());
    }

    /**
     * testIsRoot
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isRoot
     */
    public function testIsRoot(): void
    {
        $this->setAllReferences();
        $this->tree->method('getRoot')->willReturn($this->parent);

        self::assertTrue($this->parent->isRoot());
        self::assertFalse($this->node->isRoot());
    }

    /**
     * testIsDescendantOfSelfReturnsTrue
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isDescendantOf
     */
    public function testIsDescendantOfReturnsTrueForParentAndGrandparent(): void
    {
        $this->setAllReferences();
        self::assertTrue($this->node->isDescendantOf($this->parent));
        self::assertTrue($this->childTwo->isDescendantOf($this->parent));
    }

    /**
     * testIsDescendantOfReturnsTrueForParent
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isDescendantOf
     */
    public function testIsDescendantOfReturnsFalseForSelfChildrenAndGrandchildren() : void
    {
        $this->setAllReferences();
        self::assertFalse($this->node->isDescendantOf($this->node));
        self::assertFalse($this->parent->isDescendantOf($this->node));
        self::assertFalse($this->parent->isDescendantOf($this->childOne));
    }

    /**
     * testIsAncestorOfReturnsFalseForParentAndGrandparent
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isAncestorOf
     */
    public function testIsAncestorOfReturnsFalseForSelfParentAndGrandparent() : void
    {
        $this->setAllReferences();
        self::assertFalse($this->node->isAncestorOf($this->node));
        self::assertFalse($this->node->isAncestorOf($this->parent));
        self::assertFalse($this->childTwo->isAncestorOf($this->parent));
    }

    /**
     * testIsAncestorOfReturnsTrueForChildrenAndGrandChildren
     * @covers \pvc\struct\tree\node\TreenodeOrdered::isAncestorOf
     */
    public function testIsAncestorOfReturnsTrueForChildrenAndGrandChildren() : void
    {
        $this->setAllReferences();
        self::assertTrue($this->parent->isAncestorOf($this->node));
        self::assertTrue($this->parent->isAncestorOf($this->childOne));
    }
}
