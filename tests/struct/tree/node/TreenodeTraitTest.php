<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\tree\node;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use pvc\interfaces\validator\ValidatorInterface;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidNodeValueException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeIdAndParentIdCannotBeTheSameException;
use pvc\struct\tree\node\Treenode;
use pvc\struct\tree\node\TreenodeTrait;

class TreenodeTraitTest extends TestCase
{
    /**
     * @var TreenodeTrait&MockObject
     */
    protected $mockObjectWithTrait;

    public function setUp(): void
    {
        $this->mockObjectWithTrait = $this->getMockForTrait(TreenodeTrait::class);
    }

    /**
     * testSetGetNodeid
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setNodeId
     * @covers \pvc\struct\tree\node\TreenodeTrait::getNodeId
     */
    public function testSetGetNodeid(): void
    {
	    /**
	     * confirm getNodeId returns null before initialization
	     */
		self::assertNull($this->mockObjectWithTrait->getNodeId());
        $nodeid = 4;
        $this->mockObjectWithTrait->setNodeId($nodeid);
        self::assertEquals($nodeid, $this->mockObjectWithTrait->getNodeId());
    }

    /**
     * testSetBadNodeidThrowsException
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setNodeId
     * @covers \pvc\struct\tree\node\TreenodeTrait::validateNodeId
     */
    public function testSetBadNodeidThrowsException(): void
    {
        $this->expectException(InvalidNodeIdException::class);
		$this->mockObjectWithTrait->setNodeId(-2);
    }

	/**
	 * testSetNodeIdThrowsExceptionWhenParentWithSameIdIsAlreadySet
	 * @throws InvalidNodeIdException
	 * @throws InvalidParentNodeException
	 * @covers \pvc\struct\tree\node\TreenodeTrait::setNodeId
	 */
	public function testSetNodeIdThrowsExceptionWhenParentWithSameIdIsAlreadySet() : void
	{
		$this->mockObjectWithTrait->setParentId(3);
		$this->expectException(NodeIdAndParentIdCannotBeTheSameException::class);
		$this->mockObjectWithTrait->setNodeId(3);
	}

    /**
     * testSetGetParentId
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @covers \pvc\struct\tree\node\TreenodeTrait::getParentId
     * @covers \pvc\struct\tree\node\TreenodeTrait::setParentId
     */
    public function testSetGetParentId(): void
    {
		$nodeId = 0;
        $parentId = 1;
		$this->mockObjectWithTrait->setNodeId($nodeId);

        $this->mockObjectWithTrait->setParentId($parentId);
        self::assertEquals($parentId, $this->mockObjectWithTrait->getParentId());

        $this->mockObjectWithTrait->setParentId(null);
	    self::assertNull($this->mockObjectWithTrait->getParentId());
    }

    /**
     * testSetInvalidParentId
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setParentId
     */
    public function testSetInvalidParentId(): void
    {
	    $this->expectException(InvalidNodeIdException::class);
		$this->mockObjectWithTrait->setParentId(-3);
    }

    /**
     * testSetParentIdExceptionWhereParentIdHasSameIdAsChild
     * @throws InvalidNodeIdException
     * @throws InvalidParentNodeException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setParentId
     */
    public function testSetParentIdExceptionWhereParentIdHasSameIdAsChild(): void
    {
        $this->mockObjectWithTrait->setNodeId(1);
        $this->expectException(NodeIdAndParentIdCannotBeTheSameException::class);
        $this->mockObjectWithTrait->setParentId(1);
    }

	/**
	 * testIsRootReturnsFalseIfParentIdIsNotSet
	 * @throws InvalidNodeIdException
	 * @covers \pvc\struct\tree\node\TreenodeTrait::isRoot
	 */

	public function testIsRootReturnsFalseIfParentIdIsNotSet() : void
	{
		$this->mockObjectWithTrait->setNodeId(0);
		self::assertFalse($this->mockObjectWithTrait->isRoot());
	}

	/**
	 * testIsRootReturnsFalseIfParentIdIsNotNull
	 * @throws InvalidNodeIdException
	 * @throws InvalidParentNodeException
	 * @covers \pvc\struct\tree\node\TreenodeTrait::isRoot
	 */
	public function testIsRootReturnsFalseIfParentIdIsNotNull() : void
	{
		$nodeId = 0;
		$parentId = 1;
		$this->mockObjectWithTrait->setNodeId($nodeId);
		$this->mockObjectWithTrait->setParentId($parentId);
		self::assertFalse($this->mockObjectWithTrait->isRoot());
	}

	/**
	 * testIsRootReturnsTrueOnRootFixture
	 * @throws InvalidNodeIdException
	 * @throws InvalidParentNodeException
	 * @covers \pvc\struct\tree\node\TreenodeTrait::isRoot
	 */
	public function testIsRootReturnsTrueOnRootFixture() : void
	{
		$this->mockObjectWithTrait->setNodeId(0);
		$this->mockObjectWithTrait->setParentId(null);
		 self::assertTrue($this->mockObjectWithTrait->isRoot());
		//$node = new Treenode(0);
		//$node->setParentId(null);
		//self::assertTrue($node->isRoot());
	}

	/**
	 * testSetTreeidThrowsExceptionOnInvalidTreeid
	 * @throws \Exception
	 * @covers \pvc\struct\tree\node\TreenodeTrait::setTreeId
	 */
	public function testSetTreeidThrowsExceptionOnInvalidTreeid() : void
	{
		$treeid = -1;
		$this->expectException(InvalidTreeidException::class);
		$this->mockObjectWithTrait->setTreeId($treeid);
	}
    /**
     * testSetGetTreeid
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setTreeId
     * @covers \pvc\struct\tree\node\TreenodeTrait::getTreeId
     */
    public function testSetGetTreeid(): void
    {
		$treeid = 1;
        $this->mockObjectWithTrait->setTreeId($treeid);
        self::assertEquals($treeid, $this->mockObjectWithTrait->getTreeId());
    }

    /**
     * testSetGetValueValidator
     * @throws InvalidNodeIdException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setValueValidator
     * @covers \pvc\struct\tree\node\TreenodeTrait::getValueValidator
     */
    public function testSetGetValueValidator(): void
    {
        $validator = $this->createStub(ValidatorInterface::class);
        $this->mockObjectWithTrait->setValueValidator($validator);
        self::assertEquals($validator, $this->mockObjectWithTrait->getValueValidator());
    }

    /**
     * testSetValueWithNoValidatorSet
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setValue
     * @covers \pvc\struct\tree\node\TreenodeTrait::getValue
     */
    public function testSetValueWithNoValidatorSet(): void
    {
        $anyValue = 1234567;
        // test with no validator set
        $this->mockObjectWithTrait->setValue($anyValue);
        static::assertEquals($anyValue, $this->mockObjectWithTrait->getValue());
    }

    /**
     * testSetGetValueThatPassesValidation
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setValue
     * @covers \pvc\struct\tree\node\TreenodeTrait::getValue
     */
    public function testSetGetValueThatPassesValidation(): void
    {
        $valueValidator = $this->createMock(ValidatorInterface::class);
        $goodString = 'good string';
        $valueValidator->method('validate')->with($goodString)->willReturn(true);
        $this->mockObjectWithTrait->setValueValidator($valueValidator);
        $this->mockObjectWithTrait->setValue($goodString);
        static::assertEquals($goodString, $this->mockObjectWithTrait->getValue());
    }

    /**
     * testSetValueThatFailsValidation
     * @throws InvalidNodeIdException
     * @throws InvalidNodeValueException
     * @covers \pvc\struct\tree\node\TreenodeTrait::setValue
     */
    public function testSetValueThatFailsValidation(): void
    {
        $valueValidator = $this->createMock(ValidatorInterface::class);
        $badString = 'bad string';
        $valueValidator->method('validate')->with($badString)->willReturn(false);
		$this->mockObjectWithTrait->setValueValidator($valueValidator);
        $this->expectException(InvalidNodeValueException::class);
        $this->mockObjectWithTrait->setValue($badString);
    }

	/**
	 * testHydrateDehydrate
	 * @throws InvalidNodeIdException
	 * @throws InvalidNodeValueException
	 * @throws InvalidParentNodeException
	 * @covers \pvc\struct\tree\node\TreenodeTrait::hydrate
	 * @covers \pvc\struct\tree\node\TreenodeTrait::dehydrate
	 */
	public function testHydrateDehydrate() : void
	{
		$nodeid = 5;
		$parentid = 1;
		$treeid = 9;
		$value = 'some string';
		$testArray = ['nodeid' => $nodeid, 'parentid' => $parentid, 'treeid' => $treeid, 'value' => $value];

		$this->mockObjectWithTrait->hydrate($testArray);

		self::assertEquals($nodeid, $this->mockObjectWithTrait->getNodeId());
		self::assertEquals($parentid, $this->mockObjectWithTrait->getParentId());
		self::assertEquals($treeid, $this->mockObjectWithTrait->getTreeId());
		self::assertEquals($value, $this->mockObjectWithTrait->getValue());

		$resultArray = $this->mockObjectWithTrait->dehydrate();
		self::assertEqualsCanonicalizing($testArray, $resultArray);
	}

	private function makeNode(int $nodeId,
								int $parentId,
								int $treeId,
								ValidatorInterface $validator,
								int $value) : Treenode {
		$node = new Treenode($nodeId);
		$node->setParentId($parentId);
		$node->setTreeId($treeId);
		$node->setValueValidator($validator);
		$node->setValue($value);
		return $node;
	}

	/**
	 * testEqualsStrictIsTrue
	 * @covers \pvc\struct\tree\node\TreenodeTrait::equals
	 */
	public function testEquals() : void
	{
		$nodeId = 1;
		$parentId = 2;
		$treeId = 3;
		$valueValidator = $this->createStub(ValidatorInterface::class);
		$valueValidator->method('validate')->willReturn(true);
		$value = 4;

		$strict = true;

		$node1 = $this->makeNode($nodeId, $parentId, $treeId, $valueValidator, $value);
		$node2 = $this->makeNode($nodeId, $parentId, $treeId, $valueValidator, $value);

		self::assertTrue($node1->equals($node1, $strict));
		self::assertFalse($node1->equals($node2, $strict));

		$strict = false;

		self::assertTrue($node1->equals($node1, $strict));
		self::assertTrue($node1->equals($node2, $strict));
	}


}
