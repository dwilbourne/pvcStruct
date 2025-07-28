<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\tree\err;

use pvc\err\XDataTestMaster;
use pvc\struct\tree\err\_TreeXData;

class _TreeXDataTest extends XDataTestMaster
{
    /**
     * testTreeExceptionLibrary
     *
     * @covers \pvc\struct\tree\err\_TreeXData::getLocalXCodes
     * @covers \pvc\struct\tree\err\_TreeXData::getXMessageTemplates
     * @covers \pvc\struct\tree\err\AlreadySetNodeidException::__construct
     * @covers \pvc\struct\tree\err\AlreadySetRootException::__construct
     * @covers \pvc\struct\tree\err\CircularGraphException::__construct
     * @covers \pvc\struct\tree\err\DeleteInteriorNodeException::__construct
     * @covers \pvc\struct\tree\err\InvalidNodeIdException::__construct
     * @covers \pvc\struct\tree\err\InvalidValueException::__construct
     * @covers \pvc\struct\tree\err\InvalidParentNodeIdException::__construct
     * @covers \pvc\struct\tree\err\InvalidTreeidException::__construct
     * @covers \pvc\struct\tree\err\NodeNotInTreeException::__construct
     * @covers \pvc\struct\tree\err\SetTreeException::__construct
     * @covers \pvc\struct\tree\err\NodeNotEmptyHydrationException::__construct
     * @covers \pvc\struct\tree\err\TreenodeFactoryNotInitializedException
     * @covers \pvc\struct\tree\err\TreeNotInitializedException
     */
    public function testTreeExceptionLibrary(): void
    {
        $xData = new _TreeXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
