<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\lists\err;

use pvc\err\XDataTestMaster;
use pvc\struct\lists\err\_ListXData;

/**
 * Class _ListXDataTest
 */
class _ListXDataTest extends XDataTestMaster
{
    /**
     * testListExceptionLibrary
     * @covers \pvc\struct\lists\err\_ListXData::getLocalXCodes
     * @covers \pvc\struct\lists\err\_ListXData::getXMessageTemplates
     * @covers \pvc\struct\lists\err\DuplicateKeyException::__construct
     * @covers \pvc\struct\lists\err\InvalidKeyException::__construct
     * @covers \pvc\struct\lists\err\NonExistentKeyException::__construct
     */
    public function testListExceptionLibrary(): void
    {
        $xData = new _ListXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
