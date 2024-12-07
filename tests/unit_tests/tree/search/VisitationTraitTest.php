<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */

declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\search;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\search\VisitStatus;
use pvc\struct\tree\search\VisitationTrait;

class VisitationTraitTest extends TestCase
{
    protected $mock;

    public function setUp(): void
    {
        $this->mock = $this->getMockForTrait(VisitationTrait::class);
    }

    /**
     * testSetGetStatus
     * @covers \pvc\struct\tree\search\VisitationTrait::getVisitStatus
     * @covers \pvc\struct\tree\search\VisitationTrait::setVisitStatus
     * @covers \pvc\struct\tree\search\VisitationTrait::initializeVisitStatus
     */
    public function testSetGetStatus(): void
    {
        $status = VisitStatus::PARTIALLY_VISITED;
        $this->mock->setVisitStatus($status);
        self::assertEquals($status, $this->mock->getVisitStatus());
        $this->mock->initializeVisitStatus();
        self::assertEquals(VisitStatus::NEVER_VISITED, $this->mock->getVisitStatus());
    }
}
