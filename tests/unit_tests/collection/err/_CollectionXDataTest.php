<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\collection\err;

use pvc\err\XDataTestMaster;
use pvc\struct\collection\err\_CollectionXData;

/**
 * Class _CollectionXDataTest
 */
class _CollectionXDataTest extends XDataTestMaster
{
    /**
     * testListExceptionLibrary
     *
     * @covers \pvc\struct\collection\err\_CollectionXData::getLocalXCodes
     * @covers \pvc\struct\collection\err\_CollectionXData::getXMessageTemplates
     * @covers \pvc\struct\collection\err\DuplicateKeyException::__construct
     * @covers \pvc\struct\collection\err\InvalidKeyException::__construct
     * @covers \pvc\struct\collection\err\NonExistentKeyException::__construct
     */
    public function testListExceptionLibrary(): void
    {
        $xData = new _CollectionXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
