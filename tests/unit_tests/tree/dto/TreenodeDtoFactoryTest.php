<?php

/**
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 */
declare (strict_types=1);

namespace pvcTests\struct\unit_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\payload\HasPayloadInterface;
use pvc\struct\dto\PropertyMapFactory;
use pvc\struct\tree\dto\TreenodeDto;
use pvc\struct\tree\dto\TreenodeDtoFactory;

/**
 * @template PayloadType of HasPayloadInterface
 */
class TreenodeDtoFactoryTest extends TestCase
{
    /**
     * @var TreenodeDtoFactory<PayloadType>
     */
    protected TreenodeDtoFactory $factory;

    public function setUp(): void
    {
        $propertyMapFactory = $this->createMock(PropertyMapFactory::class);
        $this->factory = new TreenodeDtoFactory($propertyMapFactory);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoFactory::__construct
     */
    public function testConstruct(): void
    {
        self:
        self::assertInstanceOf(TreenodeDtoFactory::class, $this->factory);
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoFactory::makeDto
     */
    public function testMakeDto(): void
    {
        $source = [
            'nodeId' => 1,
            'parentId' => 2,
            'treeId' => null,
            'payload' => '5',
            'index' => -1,
        ];
        self::assertInstanceOf(TreenodeDto::class, $this->factory->makeDto($source));
    }

}
