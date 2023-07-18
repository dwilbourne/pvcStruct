<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\tree\err;

use PHPUnit\Framework\TestCase;
use pvc\err\XDataTestMaster;
use pvc\struct\tree\err\_TreeXData;
use pvc\struct\tree\err\AddChildException;
use pvc\struct\tree\err\AlreadySetNodeidException;
use pvc\struct\tree\err\AlreadySetParentException;
use pvc\struct\tree\err\AlreadySetRootException;
use pvc\struct\tree\err\CircularGraphException;
use pvc\struct\tree\err\DeleteChildException;
use pvc\struct\tree\err\DeleteInteriorNodeException;
use pvc\struct\tree\err\InvalidNodeIdException;
use pvc\struct\tree\err\InvalidNodeValueException;
use pvc\struct\tree\err\InvalidParentNodeException;
use pvc\struct\tree\err\InvalidTreeidException;
use pvc\struct\tree\err\NodeNotInTreeException;
use pvc\struct\tree\err\UnsetNodeValueException;

class _TreeXDataTest extends XDataTestMaster
{
    /**
     * testTreeExceptionLibrary
     * @covers \pvc\struct\tree\err\_TreeXData::getLocalXCodes
     * @covers \pvc\struct\tree\err\_TreeXData::getXMessageTemplates
     * @covers \pvc\struct\tree\err\AddChildException::__construct
     * @covers \pvc\struct\tree\err\AlreadySetNodeidException::__construct
     * @covers \pvc\struct\tree\err\AlreadySetParentException::__construct
     * @covers \pvc\struct\tree\err\AlreadySetRootException::__construct
     * @covers \pvc\struct\tree\err\BadSearchLevelsException::__construct
     * @covers \pvc\struct\tree\err\CircularGraphException::__construct
     * @covers \pvc\struct\tree\err\DeleteChildException::__construct
     * @covers \pvc\struct\tree\err\DeleteInteriorNodeException::__construct
     * @covers \pvc\struct\tree\err\InvalidNodeArrayException::__construct
     * @covers \pvc\struct\tree\err\InvalidNodeException::__construct
     * @covers \pvc\struct\tree\err\InvalidNodeIdException::__construct
     * @covers \pvc\struct\tree\err\InvalidNodeValueException::__construct
     * @covers \pvc\struct\tree\err\InvalidParentNodeException::__construct
     * @covers \pvc\struct\tree\err\InvalidTreeidException::__construct
     * @covers \pvc\struct\tree\err\NodeHasInvalidTreeidException::__construct
     * @covers \pvc\struct\tree\err\NodeIdAndParentIdCannotBeTheSameException::__construct
     * @covers \pvc\struct\tree\err\NodeNotInTreeException::__construct
     * @covers \pvc\struct\tree\err\RootCountForTreeException::__construct
     * @covers \pvc\struct\tree\err\SetChildrenException::__construct
     * @covers \pvc\struct\tree\err\SetTreeIdException::__construct
     * @covers \pvc\struct\tree\err\UnsetNodeValueException::__construct
     */
    public function testTreeExceptionLibrary(): void
    {
        $xData = new _TreeXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
