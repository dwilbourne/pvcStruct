<?php

namespace pvcTests\struct\integration_tests\tree\dto;

use PHPUnit\Framework\TestCase;
use pvc\interfaces\struct\tree\dto\TreenodeDtoInterface;
use pvc\struct\collection\CollectionFactory;
use pvc\struct\tree\dto\TreenodeDtoCollection;
use pvc\struct\tree\dto\TreenodeDtoCollectionFactory;
use pvcTests\struct\integration_tests\tree\fixture\TestUtils;
use pvcTests\struct\integration_tests\tree\fixture\TreenodeConfigurationsFixture;

class TreenodeDtoCollectionTest extends TestCase
{
    protected TreenodeDtoCollectionFactory $treenodeDtoCollectionFactory;
    protected TreenodeDtoCollection $treenodeDtoCollection;
    protected array $dtoArray;

    /**
     * @return void
     * @covers \pvc\struct\tree\node\TreenodeCollectionFactory::makeTreenodeCollection
     * @covers \pvc\struct\tree\node\TreenodeCollection::__construct
     * @covers \pvc\struct\tree\node\TreenodeCollection::count
     */
    public function testConstruct() : void
    {
        $this->treenodeDtoCollection = $this->treenodeDtoCollectionFactory->makeTreenodeDtoCollection($this->dtoArray);
        self::assertInstanceOf(TreenodeDtoCollection::class, $this->treenodeDtoCollection);
        self::assertEquals(count($this->dtoArray), count($this->treenodeDtoCollection));
    }

    /**
     * @return void
     * @covers \pvc\struct\tree\dto\TreenodeDtoCollection
     * no specific code to test because all the operations are covered by the inner iterator - this is a sanity check
     */
    public function testIteration() : void
    {
        $this->treenodeDtoCollection = $this->treenodeDtoCollectionFactory->makeTreenodeDtoCollection($this->dtoArray);
        $i = 0;
        foreach($this->treenodeDtoCollection as $dto) {
            $i++;
        }
        self::assertEquals($i, $this->treenodeDtoCollection->count());

        $filter = function (TreenodeDtoInterface $dto) : bool { return 1 === $dto->parentId; };
        $childDtos = array_filter($this->dtoArray, $filter);
        $this->treenodeDtoCollection = $this->treenodeDtoCollectionFactory->makeTreenodeDtoCollection($childDtos);
        $expectedResult = [3, 4, 5];

        $actualResult = [];
        foreach($this->treenodeDtoCollection as $dto) {
            $actualResult[] = $dto->nodeId;
        }
        self::assertEquals($expectedResult, $actualResult);
    }

    protected function setUp() : void
    {
        $ordered = false;
        $testUtils = new TestUtils($ordered);
        $fixture = new TreenodeConfigurationsFixture();
        $this->dtoArray = $testUtils->makeDtoArray($fixture);

        $collectionFactory = new CollectionFactory();
        $this->treenodeDtoCollectionFactory = new TreenodeDtoCollectionFactory($collectionFactory);
    }
}
