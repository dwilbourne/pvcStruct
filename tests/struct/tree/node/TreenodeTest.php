<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree\node;

use Mockery;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\node\err\InvalidNodeIdException;
use pvc\struct\tree\node\err\InvalidNodeValueException;
use pvc\struct\tree\node\Treenode;
use PHPUnit\Framework\TestCase;
use pvc\validator\base\ValidatorInterface;

/**
 * Class TreenodeSimpleTest
 */
class TreenodeTest extends TestCase
{

    public function testSetGetNodeId() : void
    {
        $nodeid = 0;
        $node = new Treenode($nodeid);
        static::assertEquals($nodeid, $node->getNodeId());
    }

    public function testSetNodeIdException() : void
    {
        // throw an exception if the nodeid is invalid
        $this->expectException(InvalidNodeIdException::class);
        $node = new Treenode(-2);
    }

    public function testSetInvalidParentId() : void
    {
        $node = new Treenode(2);
        $this->expectException(InvalidNodeIdException::class);
        $node->setParentId(-3);
    }

    public function testSetGetParentId() : void
    {
        $node = new Treenode(0);

        $node->setParentId(1);
        static::assertEquals(1, $node->getParentId());

        $node->setParentId(null);
        static::assertNull($node->getParentId());
    }

    public function testSetParentIdExceptionWithInvalidParentId() : void
    {
        $this->expectException(InvalidNodeIdException::class);
        $node = new Treenode(-5);
    }

    public function testSetParentIdExceptionWhereParentIdHasSameIdAsChild() : void
    {
        $node = new Treenode(1);
        $this->expectException(InvalidParentNodeException::class);
        $node->setParentId(1);
    }

    public function testSetGetTreeid() : void
    {
        $node = new Treenode(0);
        $treeid = 9;
        $node->setTreeId($treeid);
        self::assertEquals($treeid, $node->getTreeId());
    }

    public function testSetGetValue() : void
    {
        $valueValidator = Mockery::mock(ValidatorInterface::class);

        $anyValue = 1234567;

        $goodString = 'good string';
        $badString = 'bad string';

        $valueValidator->shouldReceive('validate')->with($goodString)->andReturn(true);
        $valueValidator->shouldReceive('validate')->with($badString)->andReturn(false);

        $node = new Treenode(0);

        // test with no validator set
        $node->setValue($anyValue);
        static::assertEquals($anyValue, $node->getValue());

        // test with validator set
        $node->setValueValidator($valueValidator);
        self::assertEquals($valueValidator, $node->getValueValidator());

        $node->setValue($goodString);
        static::assertEquals($goodString, $node->getValue());

        $expectedException = InvalidNodeValueException::class;
        $this->expectException($expectedException);
        $node->setValue($badString);
    }

    public function testHydrate() : void
    {
        $node = new Treenode(5);
        $testValue = 'some string';
        $valueValidator = Mockery::mock(ValidatorInterface::class);
        $valueValidator->shouldReceive('validate')->with($testValue)->andReturn(true);

        $testArray = ['nodeid' => 5, 'parentid' => 1, 'treeid' => 9, 'value' => $testValue];
        $node->hydrate($testArray);
        self::assertEquals(5, $node->getNodeId());
        self::assertEquals(1, $node->getParentId());
        self::assertEquals(9, $node->getTreeId());
        self::assertEquals($testValue, $node->getValue());
        $resultArray = $node->dehydrate();
        self::assertEquals($testArray, $resultArray);
    }
}
