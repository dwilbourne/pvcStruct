<?php
/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace tests\struct\tree\node;


use PHPUnit\Framework\TestCase;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\node\Treenode;

/**
 * Class TreenodeSimpleTest
 */
class TreenodeTest extends TestCase
{
	protected Treenode $node;

	/**
	 * testConstruct
	 * @throws InvalidNodeIdException
	 * @covers \pvc\struct\tree\node\Treenode::__construct
	 */
	public function testConstruct() : void
	{
		$nodeid = 4;
		self::assertInstanceOf(Treenode::class, new Treenode($nodeid));
	}
}
