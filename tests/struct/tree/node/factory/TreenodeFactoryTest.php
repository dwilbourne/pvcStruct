<?php
/**
 * @package: pvc
 * @author: Doug Wilbourne (dougwilbourne@gmail.com)
 * @version: 1.0
 */

namespace tests\struct\tree\node\factory;

use Mockery;
use PHPUnit\Framework\TestCase;
use pvc\struct\tree\node\factory\TreenodeFactory;
use pvc\validator\base\ValidatorInterface;

class TreenodeFactoryTest extends TestCase
{
    protected TreenodeFactory $treenodeFactory;
    /** @phpstan-ignore-next-line */
    protected $validator;

    public function setUp(): void
    {
        $this->treenodeFactory = new TreenodeFactory();
        $this->validator = Mockery::mock(ValidatorInterface::class);
        $this->treenodeFactory->setNodeIdValidator($this->validator);
    }

    public function testSetGetValidator() : void
    {
        self::assertEquals($this->validator, $this->treenodeFactory->getNodeidValidator());
    }

    public function testMakeTreenode() : void
    {
        $node = $this->treenodeFactory->makeTreenode(1);
        self::assertTrue('pvc\struct\tree\node\Treenode' == get_class($node));
    }
}
