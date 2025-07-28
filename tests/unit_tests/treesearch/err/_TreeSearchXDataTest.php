<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare(strict_types=1);

namespace pvcTests\struct\unit_tests\treesearch\err;

use pvc\err\XDataTestMaster;
use pvc\struct\treesearch\err\_TreeSearchXData;

class _TreeSearchXDataTest extends XDataTestMaster
{
    /**
     * testTreeExceptionLibrary
     *
     * @covers \pvc\struct\treesearch\err\_TreeSearchXData::getLocalXCodes
     * @covers \pvc\struct\treesearch\err\_TreeSearchXData::getXMessageTemplates
     * @covers \pvc\struct\treesearch\err\SetMaxSearchLevelsException
     * @covers \pvc\struct\treesearch\err\StartNodeUnsetException
     */
    public function testTreeSearchExceptionLibrary(): void
    {
        $xData = new _TreeSearchXData();
        self::assertTrue($this->verifylibrary($xData));
    }
}
