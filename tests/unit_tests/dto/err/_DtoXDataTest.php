<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\dto\err;

use pvc\err\XDataTestMaster;
use pvc\struct\dto\err\_DtoXData;

/**
 * Class _DtoXDataTest
 */
class _DtoXDataTest extends XDataTestMaster
{
    /**
     * testListExceptionLibrary
     * @covers \pvc\struct\dto\err\_DtoXData::getLocalXCodes
     * @covers \pvc\struct\dto\err\_DtoXData::getXMessageTemplates
     * @covers \pvc\struct\dto\err\DtoInvalidArrayKeyException
     * @covers \pvc\struct\dto\err\DtoInvalidEntityGetterException
     * @covers \pvc\struct\dto\err\DtoInvalidPropertyNameException
     * @covers \pvc\struct\dto\err\InvalidDtoClassException
     * @covers \pvc\struct\dto\err\InvalidDtoReflection
     * @covers \pvc\struct\dto\err\DtoClassDefinitionException
     */
    public function testListExceptionLibrary(): void
    {
        $xData = new _DtoXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
