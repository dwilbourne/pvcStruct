<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\collection\err;

use pvc\err\XDataTestMaster;
use pvc\struct\collection\err\_ListXData;

/**
 * Class _ListXDataTest
 */
class _ListXDataTest extends XDataTestMaster
{
    /**
     * testListExceptionLibrary
     * @covers \pvc\struct\collection\err\_ListXData::getLocalXCodes
     * @covers \pvc\struct\collection\err\_ListXData::getXMessageTemplates
     * @covers \pvc\struct\collection\err\DuplicateKeyException::__construct
     * @covers \pvc\struct\collection\err\InvalidKeyException::__construct
     * @covers \pvc\struct\collection\err\NonExistentKeyException::__construct
     */
    public function testListExceptionLibrary(): void
    {
        $xData = new _ListXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
